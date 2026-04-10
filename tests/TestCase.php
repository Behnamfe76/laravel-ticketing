<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests;

use Fereydooni\LaravelTicketing\Providers\TicketingServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            TicketingServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('ticketing.features.portal', true);
        $app['config']->set('ticketing.features.staff', true);
        $app['config']->set('ticketing.features.api', true);
    }
}
