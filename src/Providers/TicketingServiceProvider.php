<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Providers;

use Fereydooni\LaravelTicketing\Console\Commands\InstallTicketingCommand;
use Fereydooni\LaravelTicketing\Actions\Assignments\AssignTicketAction;
use Fereydooni\LaravelTicketing\Actions\Replies\AddReplyAction;
use Fereydooni\LaravelTicketing\Actions\Tickets\CreateTicketAction;
use Fereydooni\LaravelTicketing\Actions\Tickets\TransitionTicketAction;
use Fereydooni\LaravelTicketing\Contracts\Auth\MapsTicketRoles;
use Fereydooni\LaravelTicketing\Contracts\Automation\ComputesSLADeadlines;
use Fereydooni\LaravelTicketing\Contracts\Automation\EvaluatesAutomationRules;
use Fereydooni\LaravelTicketing\Contracts\Mail\BuildsOutboundTicketMail;
use Fereydooni\LaravelTicketing\Contracts\Mail\ProcessesInboundTicketMail;
use Fereydooni\LaravelTicketing\Contracts\MultiTenancy\ResolvesTenantContext;
use Fereydooni\LaravelTicketing\Contracts\Notifications\ResolvesNotificationRecipients;
use Fereydooni\LaravelTicketing\Contracts\Reporting\PublishesTicketMetrics;
use Fereydooni\LaravelTicketing\Contracts\Search\SearchesTickets;
use Fereydooni\LaravelTicketing\Contracts\Tickets\AddsTicketReplies;
use Fereydooni\LaravelTicketing\Contracts\Tickets\AssignsTickets;
use Fereydooni\LaravelTicketing\Contracts\Tickets\AttachmentStorage as AttachmentStorageContract;
use Fereydooni\LaravelTicketing\Contracts\Tickets\CreatesTickets;
use Fereydooni\LaravelTicketing\Contracts\Tickets\ResolvesAttachmentStorage;
use Fereydooni\LaravelTicketing\Contracts\Tickets\TransitionsTickets;
use Fereydooni\LaravelTicketing\Listeners\DispatchTicketNotifications;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Policies\TicketPolicy;
use Fereydooni\LaravelTicketing\Repositories\Search\EloquentTicketSearchRepository;
use Fereydooni\LaravelTicketing\Actions\Reporting\PublishTicketMetricsAction;
use Fereydooni\LaravelTicketing\Services\Attachments\AttachmentManager;
use Fereydooni\LaravelTicketing\Services\Email\InboundTicketMailProcessor;
use Fereydooni\LaravelTicketing\Services\SLA\EscalationEngine;
use Fereydooni\LaravelTicketing\Services\SLA\SLADeadlineCalculator;
use Fereydooni\LaravelTicketing\Support\Auth\ConfigRoleMapper;
use Fereydooni\LaravelTicketing\Support\Notifications\ConfigNotificationRecipientResolver;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Fereydooni\LaravelTicketing\Services\Tenancy\TenantContext;
use Illuminate\Support\ServiceProvider;

class TicketingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom($this->packagePath('config/ticketing.php'), 'ticketing');
        $this->mergeConfigFrom(
            $this->packagePath('config/ticketing-permissions.php'),
            'ticketing-permissions'
        );

        $this->app->singleton(TenantContext::class);

        $this->app->singleton(ResolvesTenantContext::class, function (Application $app): ResolvesTenantContext {
            $resolver = config('ticketing.tenancy.resolver');

            return is_string($resolver) && $resolver !== ''
                ? $app->make($resolver)
                : $app->make(TenantContext::class);
        });

        $this->app->singleton(MapsTicketRoles::class, ConfigRoleMapper::class);
        $this->app->singleton(ResolvesNotificationRecipients::class, ConfigNotificationRecipientResolver::class);
        $this->app->singleton(AttachmentManager::class);
        $this->app->alias(AttachmentManager::class, ResolvesAttachmentStorage::class);
        $this->app->alias(AttachmentManager::class, AttachmentStorageContract::class);
        $this->app->bind(CreatesTickets::class, CreateTicketAction::class);
        $this->app->bind(AddsTicketReplies::class, AddReplyAction::class);
        $this->app->bind(AssignsTickets::class, AssignTicketAction::class);
        $this->app->bind(TransitionsTickets::class, TransitionTicketAction::class);
        $this->app->bind(SearchesTickets::class, EloquentTicketSearchRepository::class);
        $this->app->bind(ComputesSLADeadlines::class, SLADeadlineCalculator::class);
        $this->app->bind(EvaluatesAutomationRules::class, EscalationEngine::class);
        $this->app->bind(ProcessesInboundTicketMail::class, InboundTicketMailProcessor::class);
        $this->app->bind(BuildsOutboundTicketMail::class, InboundTicketMailProcessor::class);
        $this->app->bind(PublishesTicketMetrics::class, PublishTicketMetricsAction::class);
    }

    public function boot(): void
    {
        if (config('ticketing.migrations.load', true)) {
            $this->loadMigrationsFrom($this->packagePath('database/migrations'));
        }

        $this->loadViewsFrom($this->packagePath('resources/views'), 'ticketing');
        $this->loadTranslationsFrom($this->packagePath('resources/lang'), 'ticketing');
        $this->registerPolicies();
        $this->registerRoutes();
        $this->registerNotifications();

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            $this->packagePath('config/ticketing.php') => config_path('ticketing.php'),
            $this->packagePath('config/ticketing-permissions.php') => config_path('ticketing-permissions.php'),
        ], 'ticketing-config');

        $this->publishes([
            $this->packagePath('database/migrations') => database_path('migrations'),
        ], 'ticketing-migrations');

        $this->publishes([
            $this->packagePath('resources/lang') => lang_path('vendor/ticketing'),
        ], 'ticketing-lang');

        $this->publishes([
            $this->packagePath('resources/views') => resource_path('views/vendor/ticketing'),
        ], 'ticketing-views');

        $this->commands([
            InstallTicketingCommand::class,
        ]);
    }

    protected function packagePath(string $path = ''): string
    {
        $basePath = dirname(__DIR__, 2);

        if ($path === '') {
            return $basePath;
        }

        return $basePath . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Ticket::class, TicketPolicy::class);
    }

    /**
     * Register each HTTP adapter only when both its feature switch and its route group are on.
     */
    protected function registerRoutes(): void
    {
        $groups = [
            'portal' => ['prefix' => 'tickets', 'middleware' => ['web', 'auth'], 'name' => 'ticketing.portal.'],
            'staff' => ['prefix' => 'staff/tickets', 'middleware' => ['web', 'auth'], 'name' => 'ticketing.staff.'],
            'api' => ['prefix' => 'api/ticketing', 'middleware' => ['api', 'auth'], 'name' => 'ticketing.api.'],
        ];

        foreach ($groups as $group => $defaults) {
            $route = (array) config("ticketing.routes.{$group}", []);

            if (config("ticketing.features.{$group}") !== true || ($route['enabled'] ?? false) !== true) {
                continue;
            }

            Route::middleware($route['middleware'] ?? $defaults['middleware'])
                ->prefix($route['prefix'] ?? $defaults['prefix'])
                ->name($defaults['name'])
                ->group($this->packagePath("routes/{$group}.php"));
        }
    }

    protected function registerNotifications(): void
    {
        if (config('ticketing.features.notifications') === true) {
            Event::subscribe(DispatchTicketNotifications::class);
        }
    }
}
