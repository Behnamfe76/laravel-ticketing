<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Console\Commands;

use Illuminate\Console\Command;

class InstallTicketingCommand extends Command
{
    protected $signature = 'ticketing:install
                            {--migrate : Run migrations after publishing assets}
                            {--force : Overwrite publishable files when supported}';

    protected $description = 'Publish Laravel Ticketing assets and print setup guidance.';

    public function handle(): int
    {
        $force = $this->option('force') ? ['--force' => true] : [];

        $this->components->info('Publishing Laravel Ticketing configuration...');
        $this->call('vendor:publish', ['--tag' => 'ticketing-config'] + $force);

        $this->components->info('Publishing Laravel Ticketing migrations...');
        $this->call('vendor:publish', ['--tag' => 'ticketing-migrations'] + $force);

        $this->components->info('Publishing Laravel Ticketing translations...');
        $this->call('vendor:publish', ['--tag' => 'ticketing-lang'] + $force);

        if ($this->option('migrate')) {
            $this->components->info('Running migrations...');
            $this->call('migrate');
        }

        $this->newLine();
        $this->components->info('Laravel Ticketing installation completed.');
        $this->line('Next steps:');
        $this->line('  1. Review config/ticketing.php for model, route, and feature toggles.');
        $this->line('  2. Review config/ticketing-permissions.php for ability mapping.');
        $this->line('  3. Run php artisan migrate if you did not use --migrate.');
        $this->line('  4. Enable the portal, staff, API, mail, and queue features you need.');

        return self::SUCCESS;
    }
}
