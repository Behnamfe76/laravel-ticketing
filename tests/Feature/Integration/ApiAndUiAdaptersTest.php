<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Feature\Integration;

use Fereydooni\LaravelTicketing\Models\Category;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Tests\Concerns\SetsUpTicketingDatabase;
use Fereydooni\LaravelTicketing\Tests\TestCase;

class ApiAndUiAdaptersTest extends TestCase
{
    use SetsUpTicketingDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateTicketingDatabase();

        Status::query()->create(['name' => 'Open', 'slug' => 'open', 'is_default' => true]);
        Category::query()->create(['name' => 'Support', 'slug' => 'support']);
    }

    public function test_api_can_create_reply_assign_transition_and_list_metadata(): void
    {
        $actor = $this->user(['ticket.assign', 'ticket.manage']);

        $this->actingAs($actor)
            ->postJson('/api/ticketing/tickets', ['subject' => 'API ticket'])
            ->assertCreated()
            ->assertJsonPath('data.subject', 'API ticket');

        $ticket = Ticket::query()->firstOrFail();

        $this->actingAs($actor)
            ->postJson('/api/ticketing/tickets/' . $ticket->getKey() . '/replies', ['body' => 'API reply'])
            ->assertCreated();

        $this->actingAs($actor)
            ->postJson('/api/ticketing/tickets/' . $ticket->getKey() . '/assignments', ['target_type' => 'user', 'target_id' => $actor->getKey()])
            ->assertCreated();

        $this->actingAs($actor)
            ->postJson('/api/ticketing/tickets/' . $ticket->getKey() . '/status-transitions', ['transition' => 'resolve'])
            ->assertOk()
            ->assertJsonPath('data.id', $ticket->getKey());

        $this->actingAs($actor)
            ->getJson('/api/ticketing/metadata')
            ->assertOk()
            ->assertJsonCount(1, 'categories');
    }

    public function test_portal_and_staff_adapter_indexes_return_ticket_resources(): void
    {
        $actor = $this->user();

        $this->actingAs($actor)
            ->postJson('/tickets', ['subject' => 'Portal visible'])
            ->assertCreated();

        $this->actingAs($actor)
            ->getJson('/tickets')
            ->assertOk()
            ->assertJsonPath('data.0.subject', 'Portal visible');

        $this->actingAs($actor)
            ->getJson('/staff/tickets')
            ->assertOk()
            ->assertJsonPath('data.0.subject', 'Portal visible');
    }
}
