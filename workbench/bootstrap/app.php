<?php

declare(strict_types=1);

use Fereydooni\LaravelTicketing\Providers\TicketingServiceProvider;
use Orchestra\Testbench\Workbench\Workbench;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

return Workbench::startWithProviders([
    TicketingServiceProvider::class,
]);
