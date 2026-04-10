<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Providers;

use Fereydooni\LaravelTicketing\Console\Commands\InstallTicketingCommand;
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
    }

    public function boot(): void
    {
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
}
