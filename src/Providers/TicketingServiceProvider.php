<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Providers;

use Fereydooni\LaravelTicketing\Console\Commands\InstallTicketingCommand;
use Fereydooni\LaravelTicketing\Actions\Assignments\AssignTicketAction;
use Fereydooni\LaravelTicketing\Actions\Replies\AddReplyAction;
use Fereydooni\LaravelTicketing\Actions\Tickets\CreateTicketAction;
use Fereydooni\LaravelTicketing\Contracts\Auth\MapsTicketRoles;
use Fereydooni\LaravelTicketing\Contracts\Automation\ComputesSLADeadlines;
use Fereydooni\LaravelTicketing\Contracts\Automation\EvaluatesAutomationRules;
use Fereydooni\LaravelTicketing\Contracts\MultiTenancy\ResolvesTenantContext;
use Fereydooni\LaravelTicketing\Contracts\Notifications\ResolvesNotificationRecipients;
use Fereydooni\LaravelTicketing\Contracts\Search\SearchesTickets;
use Fereydooni\LaravelTicketing\Contracts\Tickets\AddsTicketReplies;
use Fereydooni\LaravelTicketing\Contracts\Tickets\AssignsTickets;
use Fereydooni\LaravelTicketing\Contracts\Tickets\AttachmentStorage as AttachmentStorageContract;
use Fereydooni\LaravelTicketing\Contracts\Tickets\CreatesTickets;
use Fereydooni\LaravelTicketing\Contracts\Tickets\ResolvesAttachmentStorage;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Policies\TicketPolicy;
use Fereydooni\LaravelTicketing\Repositories\Search\EloquentTicketSearchRepository;
use Fereydooni\LaravelTicketing\Services\Attachments\AttachmentManager;
use Fereydooni\LaravelTicketing\Services\SLA\EscalationEngine;
use Fereydooni\LaravelTicketing\Services\SLA\SLADeadlineCalculator;
use Fereydooni\LaravelTicketing\Support\Auth\ConfigRoleMapper;
use Fereydooni\LaravelTicketing\Support\Notifications\ConfigNotificationRecipientResolver;
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
        $this->app->alias(TenantContext::class, ResolvesTenantContext::class);

        $this->app->singleton(MapsTicketRoles::class, ConfigRoleMapper::class);
        $this->app->singleton(ResolvesNotificationRecipients::class, ConfigNotificationRecipientResolver::class);
        $this->app->singleton(AttachmentManager::class);
        $this->app->alias(AttachmentManager::class, ResolvesAttachmentStorage::class);
        $this->app->alias(AttachmentManager::class, AttachmentStorageContract::class);
        $this->app->bind(CreatesTickets::class, CreateTicketAction::class);
        $this->app->bind(AddsTicketReplies::class, AddReplyAction::class);
        $this->app->bind(AssignsTickets::class, AssignTicketAction::class);
        $this->app->bind(SearchesTickets::class, EloquentTicketSearchRepository::class);
        $this->app->bind(ComputesSLADeadlines::class, SLADeadlineCalculator::class);
        $this->app->bind(EvaluatesAutomationRules::class, EscalationEngine::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom($this->packagePath('database/migrations'));
        $this->registerPolicies();
        $this->registerRoutes();

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

    protected function registerRoutes(): void
    {
        $portal = (array) config('ticketing.routes.portal', []);
        $staff = (array) config('ticketing.routes.staff', []);

        if (($portal['enabled'] ?? false) === true) {
            Route::middleware($portal['middleware'] ?? ['web', 'auth'])
                ->prefix($portal['prefix'] ?? 'tickets')
                ->name('ticketing.portal.')
                ->group($this->packagePath('routes/portal.php'));
        }

        if (($staff['enabled'] ?? false) === true) {
            Route::middleware($staff['middleware'] ?? ['web', 'auth'])
                ->prefix($staff['prefix'] ?? 'staff/tickets')
                ->name('ticketing.staff.')
                ->group($this->packagePath('routes/staff.php'));
        }
    }
}
