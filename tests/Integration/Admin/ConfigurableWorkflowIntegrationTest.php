<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Integration\Admin;

use Fereydooni\LaravelTicketing\Actions\Tickets\CreateTicketAction;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Repositories\Eloquent\TenantScopedTicketRepository;
use Fereydooni\LaravelTicketing\Services\Tenancy\TenantContext;
use Fereydooni\LaravelTicketing\Services\Tenancy\TenantScopeManager;
use Fereydooni\LaravelTicketing\Tests\Concerns\SetsUpTicketingDatabase;
use Fereydooni\LaravelTicketing\Tests\TestCase;

class ConfigurableWorkflowIntegrationTest extends TestCase
{
    use SetsUpTicketingDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateTicketingDatabase();

        Status::query()->create(['name' => 'Open', 'slug' => 'open', 'is_default' => true]);
    }

    public function test_tenant_context_scopes_ticket_queries_and_stamps_models(): void
    {
        app(TenantContext::class)->set('tenant-a');
        $tenantTicket = app(CreateTicketAction::class)->create(['subject' => 'Tenant A'], $this->user());

        app(TenantContext::class)->set('tenant-b');
        app(CreateTicketAction::class)->create(['subject' => 'Tenant B'], $this->user());

        app(TenantContext::class)->set('tenant-a');

        $this->assertSame([$tenantTicket->getKey()], app(TenantScopedTicketRepository::class)->query()->pluck('id')->all());

        $ticket = new Ticket(['subject' => 'Stamped']);
        app(TenantScopeManager::class)->stamp($ticket);

        $this->assertSame('tenant-a', $ticket->tenant_id);
    }
}
