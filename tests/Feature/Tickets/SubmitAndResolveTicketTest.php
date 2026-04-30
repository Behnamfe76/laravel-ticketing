<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Feature\Tickets;

use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Tests\Concerns\SetsUpTicketingDatabase;
use Fereydooni\LaravelTicketing\Tests\TestCase;

class SubmitAndResolveTicketTest extends TestCase
{
    use SetsUpTicketingDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateTicketingDatabase();

        Status::query()->create(['name' => 'Open', 'slug' => 'open', 'is_default' => true]);
    }

    public function test_portal_user_can_submit_ticket_and_staff_can_resolve_it(): void
    {
        $requester = $this->user();
        $staff = $this->user(['ticket.manage']);

        $this->actingAs($requester)
            ->postJson('/tickets', ['subject' => 'Cannot login'])
            ->assertCreated()
            ->assertJsonPath('data.subject', 'Cannot login');

        $ticket = Ticket::query()->firstOrFail();

        $this->actingAs($staff)
            ->postJson('/staff/tickets/' . $ticket->getKey() . '/resolve')
            ->assertOk()
            ->assertJsonPath('data.id', $ticket->getKey());

        $this->assertNotNull($ticket->refresh()->resolved_at);
    }
}
