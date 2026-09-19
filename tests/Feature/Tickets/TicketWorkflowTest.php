<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Feature\Tickets;

use Fereydooni\LaravelTicketing\Actions\Tickets\CreateTicketAction;
use Fereydooni\LaravelTicketing\Contracts\Tickets\TransitionsTickets;
use Fereydooni\LaravelTicketing\Contracts\Tickets\UpdatesTickets;
use Fereydooni\LaravelTicketing\Events\TicketStatusChanged;
use Fereydooni\LaravelTicketing\Events\TicketUpdated;
use Fereydooni\LaravelTicketing\Models\Category;
use Fereydooni\LaravelTicketing\Models\Priority;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Tests\Concerns\SetsUpTicketingDatabase;
use Fereydooni\LaravelTicketing\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;

class TicketWorkflowTest extends TestCase
{
    use SetsUpTicketingDatabase;

    protected Status $open;

    protected Status $pending;

    protected Status $resolved;

    protected Status $closed;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateTicketingDatabase();

        $this->open = Status::query()->create(['name' => 'Open', 'slug' => 'open', 'kind' => 'open', 'is_default' => true, 'sort_order' => 1, 'transitions' => ['pending', 'resolved']]);
        $this->pending = Status::query()->create(['name' => 'Pending', 'slug' => 'pending', 'kind' => 'pending', 'sort_order' => 2]);
        $this->resolved = Status::query()->create(['name' => 'Resolved', 'slug' => 'resolved', 'kind' => 'resolved', 'is_terminal' => true, 'sort_order' => 3]);
        $this->closed = Status::query()->create(['name' => 'Closed', 'slug' => 'closed', 'kind' => 'closed', 'is_terminal' => true, 'sort_order' => 4]);
    }

    public function test_status_changes_follow_the_allowed_transitions(): void
    {
        $ticket = $this->ticket();

        $this->expectException(ValidationException::class);

        app(TransitionsTickets::class)->changeStatus($ticket, $this->closed->getKey());
    }

    public function test_a_status_without_transitions_may_move_anywhere(): void
    {
        $ticket = app(TransitionsTickets::class)->changeStatus($this->ticket(), $this->pending->getKey());

        $ticket = app(TransitionsTickets::class)->changeStatus($ticket, $this->closed->getKey());

        $this->assertSame($this->closed->getKey(), $ticket->status_id);
        $this->assertNotNull($ticket->resolved_at);
        $this->assertNotNull($ticket->closed_at);
    }

    public function test_moving_to_a_terminal_status_resolves_and_back_to_open_clears_it(): void
    {
        Event::fake([TicketStatusChanged::class]);
        $actor = $this->user();
        $ticket = app(TransitionsTickets::class)->changeStatus($this->ticket(), $this->resolved->getKey(), $actor);

        $this->assertNotNull($ticket->resolved_at);
        $this->assertNull($ticket->closed_at);
        Event::assertDispatched(TicketStatusChanged::class, fn (TicketStatusChanged $event): bool => $event->fromStatusId === $this->open->getKey()
            && $event->toStatusId === $this->resolved->getKey()
            && $event->actor?->is($actor) === true);

        $ticket = app(TransitionsTickets::class)->changeStatus($ticket, $this->open->getKey());

        $this->assertNull($ticket->resolved_at);
        $this->assertSame(
            ['ticket.created', 'ticket.status_changed', 'ticket.status_changed'],
            $ticket->auditRecords()->orderBy('id')->pluck('event_name')->all(),
        );
    }

    public function test_an_unknown_status_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        app(TransitionsTickets::class)->changeStatus($this->ticket(), 9999);
    }

    public function test_resolve_and_reopen_move_the_ticket_to_the_matching_statuses(): void
    {
        $ticket = app(TransitionsTickets::class)->resolve($this->ticket());
        $this->assertSame($this->resolved->getKey(), $ticket->status_id);

        $ticket = app(TransitionsTickets::class)->reopen($ticket);
        $this->assertSame($this->open->getKey(), $ticket->status_id);
        $this->assertNull($ticket->resolved_at);
    }

    public function test_update_writes_only_editable_fields_and_records_the_changes(): void
    {
        Event::fake([TicketUpdated::class]);
        $priority = Priority::query()->create(['name' => 'High', 'slug' => 'high']);
        $ticket = $this->ticket();

        $ticket = app(UpdatesTickets::class)->update($ticket, [
            'subject' => 'Renamed',
            'priority_id' => $priority->getKey(),
            'requester_id' => 999,
            'number' => 'HIJACKED',
        ], $this->user());

        $this->assertSame('Renamed', $ticket->subject);
        $this->assertSame($priority->getKey(), $ticket->priority_id);
        $this->assertNotSame('HIJACKED', $ticket->number);
        $this->assertNotSame(999, $ticket->requester_id);
        Event::assertDispatched(TicketUpdated::class, fn (TicketUpdated $event): bool => array_keys($event->changes) === ['subject', 'priority_id']);
    }

    public function test_update_rejects_taxonomy_ids_that_do_not_exist(): void
    {
        $this->expectException(ValidationException::class);

        app(UpdatesTickets::class)->update($this->ticket(), ['category_id' => 9999]);
    }

    public function test_create_rejects_inactive_categories(): void
    {
        $category = Category::query()->create(['name' => 'Old', 'slug' => 'old', 'is_active' => false]);

        $this->expectException(ValidationException::class);

        app(CreateTicketAction::class)->create(['subject' => 'Help', 'category_id' => $category->getKey()], $this->user());
    }

    public function test_update_hands_status_changes_to_the_workflow(): void
    {
        $ticket = app(UpdatesTickets::class)->update($this->ticket(), ['status_id' => $this->pending->getKey()]);

        $this->assertSame($this->pending->getKey(), $ticket->status_id);
    }

    public function test_the_api_update_requires_manage_and_requesters_cannot_triage_their_own_ticket(): void
    {
        $priority = Priority::query()->create(['name' => 'High', 'slug' => 'high']);
        $requester = $this->user();
        $staff = $this->user(['ticket.manage']);

        $this->actingAs($requester)
            ->postJson('/api/ticketing/tickets', ['subject' => 'Help', 'priority_id' => $priority->getKey()])
            ->assertCreated()
            ->assertJsonPath('data.priority_id', null);

        $ticket = Ticket::query()->firstOrFail();

        $this->actingAs($requester)
            ->patchJson('/api/ticketing/tickets/' . $ticket->getKey(), ['priority_id' => $priority->getKey()])
            ->assertForbidden();

        $this->actingAs($staff)
            ->patchJson('/api/ticketing/tickets/' . $ticket->getKey(), ['priority_id' => $priority->getKey(), 'status_id' => $this->pending->getKey()])
            ->assertOk()
            ->assertJsonPath('data.priority_id', $priority->getKey())
            ->assertJsonPath('data.status_id', $this->pending->getKey());

        $this->actingAs($staff)
            ->patchJson('/api/ticketing/tickets/' . $ticket->getKey(), ['category_id' => 9999])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category_id');
    }

    public function test_the_api_status_transition_accepts_a_target_status(): void
    {
        $staff = $this->user(['ticket.manage']);
        $ticket = $this->ticket();

        $this->actingAs($staff)
            ->postJson('/api/ticketing/tickets/' . $ticket->getKey() . '/status-transitions', ['transition' => 'status', 'status_id' => $this->pending->getKey()])
            ->assertOk()
            ->assertJsonPath('data.status_id', $this->pending->getKey());

        $this->actingAs($staff)
            ->postJson('/api/ticketing/tickets/' . $ticket->getKey() . '/status-transitions', ['transition' => 'status', 'status_id' => $this->open->getKey()])
            ->assertOk();

        $this->actingAs($staff)
            ->postJson('/api/ticketing/tickets/' . $ticket->getKey() . '/status-transitions', ['transition' => 'status', 'status_id' => $this->closed->getKey()])
            ->assertUnprocessable();
    }

    protected function ticket(): Ticket
    {
        return app(CreateTicketAction::class)->create(['subject' => 'Help'], $this->user());
    }
}
