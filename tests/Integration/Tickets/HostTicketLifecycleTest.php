<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Integration\Tickets;

use Fereydooni\LaravelTicketing\Actions\Tickets\CreateTicketAction;
use Fereydooni\LaravelTicketing\Contracts\Tickets\CreatesTickets;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Tests\Concerns\SetsUpTicketingDatabase;
use Fereydooni\LaravelTicketing\Tests\TestCase;

class HostTicketLifecycleTest extends TestCase
{
    use SetsUpTicketingDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateTicketingDatabase();

        Status::query()->create(['name' => 'Open', 'slug' => 'open', 'is_default' => true]);
    }

    public function test_host_app_can_resolve_create_contract_from_container(): void
    {
        $this->assertInstanceOf(CreateTicketAction::class, app(CreatesTickets::class));

        $ticket = app(CreatesTickets::class)->create(['subject' => 'Created from workflow'], $this->user());

        $this->assertSame('workflow', $ticket->source);
        $this->assertDatabaseHas('ticketing_audit_records', ['event_name' => 'ticket.created']);
    }
}
