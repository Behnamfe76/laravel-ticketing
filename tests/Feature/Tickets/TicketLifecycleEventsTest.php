<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Feature\Tickets;

use Fereydooni\LaravelTicketing\Actions\Assignments\AssignTicketAction;
use Fereydooni\LaravelTicketing\Actions\Replies\AddReplyAction;
use Fereydooni\LaravelTicketing\Actions\Tickets\CreateTicketAction;
use Fereydooni\LaravelTicketing\Contracts\Tickets\TransitionsTickets;
use Fereydooni\LaravelTicketing\Events\TicketAssigned;
use Fereydooni\LaravelTicketing\Events\TicketCreated;
use Fereydooni\LaravelTicketing\Events\TicketReopened;
use Fereydooni\LaravelTicketing\Events\TicketReplyAdded;
use Fereydooni\LaravelTicketing\Events\TicketResolved;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Tests\Concerns\SetsUpTicketingDatabase;
use Fereydooni\LaravelTicketing\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use RuntimeException;

class TicketLifecycleEventsTest extends TestCase
{
    use SetsUpTicketingDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateTicketingDatabase();

        Status::query()->create(['name' => 'Open', 'slug' => 'open', 'is_default' => true]);
    }

    public function test_every_lifecycle_action_dispatches_its_event_with_the_actor(): void
    {
        Event::fake([TicketCreated::class, TicketReplyAdded::class, TicketAssigned::class, TicketResolved::class, TicketReopened::class]);
        $actor = $this->user();

        $ticket = app(CreateTicketAction::class)->create(['subject' => 'Help'], $actor);
        $entry = app(AddReplyAction::class)->add($ticket, ['body' => 'Details'], $actor);
        app(AssignTicketAction::class)->assign($ticket, ['target_type' => 'user', 'target_id' => $actor->getKey()], $actor);
        app(TransitionsTickets::class)->resolve($ticket, $actor);
        app(TransitionsTickets::class)->reopen($ticket, $actor);

        Event::assertDispatched(TicketCreated::class, fn (TicketCreated $event): bool => $event->actor?->is($actor) === true);
        Event::assertDispatched(TicketReplyAdded::class, fn (TicketReplyAdded $event): bool => $event->entry->is($entry) && $event->actor?->is($actor) === true);
        Event::assertDispatched(TicketAssigned::class, fn (TicketAssigned $event): bool => $event->assignment->target_id === (string) $actor->getKey());
        Event::assertDispatched(TicketResolved::class);
        Event::assertDispatched(TicketReopened::class);
    }

    public function test_resolve_and_reopen_are_audited_from_the_api(): void
    {
        $staff = $this->user(['ticket.manage']);
        $ticket = app(CreateTicketAction::class)->create(['subject' => 'Help'], $this->user());

        $this->actingAs($staff)
            ->postJson('/api/ticketing/tickets/' . $ticket->getKey() . '/status-transitions', ['transition' => 'resolve'])
            ->assertOk();
        $this->actingAs($staff)
            ->postJson('/api/ticketing/tickets/' . $ticket->getKey() . '/status-transitions', ['transition' => 'reopen'])
            ->assertOk();

        $this->assertSame(
            ['ticket.created', 'ticket.resolved', 'ticket.reopened'],
            $ticket->auditRecords()->orderBy('id')->pluck('event_name')->all(),
        );
        $this->assertNull($ticket->refresh()->resolved_at);
    }

    public function test_events_are_not_dispatched_when_the_surrounding_transaction_rolls_back(): void
    {
        $dispatched = [];
        Event::listen(TicketCreated::class, function () use (&$dispatched): void {
            $dispatched[] = TicketCreated::class;
        });

        try {
            DB::transaction(function (): void {
                app(CreateTicketAction::class)->create(['subject' => 'Rolled back'], $this->user());

                throw new RuntimeException('host failure after creating the ticket');
            });
        } catch (RuntimeException) {
        }

        $this->assertSame([], $dispatched);
        $this->assertSame(0, Ticket::query()->count());
    }
}
