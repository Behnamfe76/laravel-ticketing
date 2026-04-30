<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Integration;

use Fereydooni\LaravelTicketing\Contracts\MultiTenancy\ResolvesTenantContext;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class PackageBootTest extends TestCase
{
    public function test_package_registers_config_contracts_and_migrations(): void
    {
        Artisan::call('migrate');

        $this->assertSame(Ticket::class, Config::get('ticketing.models.ticket'));
        $this->assertTrue($this->app->bound(ResolvesTenantContext::class));
        $this->assertTrue(Schema::hasTable('ticketing_tickets'));
        $this->assertTrue(Schema::hasTable('ticketing_conversation_entries'));
        $this->assertTrue(Schema::hasTable('ticketing_audit_records'));
    }
}
