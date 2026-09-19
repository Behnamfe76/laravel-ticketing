<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Integration;

use Fereydooni\LaravelTicketing\Events\TicketCreated;
use Fereydooni\LaravelTicketing\Events\TicketReplyAdded;
use Fereydooni\LaravelTicketing\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/**
 * A host that brings its own HTTP layer, migrations, and notification pipeline.
 */
class HostOptOutTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        // Package defaults for the HTTP adapters, which the base TestCase turns on.
        $app['config']->set('ticketing.features.portal', false);
        $app['config']->set('ticketing.features.staff', false);
        $app['config']->set('ticketing.features.api', false);

        $app['config']->set('ticketing.features.notifications', false);
        $app['config']->set('ticketing.migrations.load', false);
    }

    public function test_no_routes_are_registered_by_default(): void
    {
        $this->assertFalse(Route::has('ticketing.portal.tickets.index'));
        $this->assertFalse(Route::has('ticketing.staff.tickets.index'));
        $this->assertFalse(Route::has('ticketing.api.tickets.index'));
    }

    public function test_a_route_group_needs_its_feature_switch_as_well_as_its_route_flag(): void
    {
        $this->assertTrue(config('ticketing.routes.portal.enabled'));
        $this->assertFalse(Route::has('ticketing.portal.tickets.store'));
    }

    public function test_migrations_are_not_loaded_when_the_host_opts_out(): void
    {
        Artisan::call('migrate');

        $this->assertFalse(Schema::hasTable('ticketing_tickets'));
    }

    public function test_built_in_notification_listeners_are_not_registered(): void
    {
        $this->assertFalse(Event::hasListeners(TicketCreated::class));
        $this->assertFalse(Event::hasListeners(TicketReplyAdded::class));
    }
}
