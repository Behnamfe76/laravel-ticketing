<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Feature\Tickets;

use Fereydooni\LaravelTicketing\Actions\Tickets\CreateTicketAction;
use Fereydooni\LaravelTicketing\Contracts\Tickets\ManagesWatchers;
use Fereydooni\LaravelTicketing\Events\TicketWatcherAdded;
use Fereydooni\LaravelTicketing\Events\TicketWatcherRemoved;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Policies\TicketPolicy;
use Fereydooni\LaravelTicketing\Tests\Concerns\SetsUpTicketingDatabase;
use Fereydooni\LaravelTicketing\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class TicketWatchersTest extends TestCase
{
    use SetsUpTicketingDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateTicketingDatabase();

        Status::query()->create(['name' => 'Open', 'slug' => 'open', 'is_default' => true]);
    }

    public function test_watching_is_idempotent_and_updates_preferences(): void
    {
        Event::fake([TicketWatcherAdded::class]);
        $ticket = $this->ticket();
        $watcher = $this->user();

        app(ManagesWatchers::class)->watch($ticket, $watcher);
        $row = app(ManagesWatchers::class)->watch($ticket, $watcher, 'watcher', ['mail' => false]);

        $this->assertSame(1, $ticket->watchers()->count());
        $this->assertSame(['mail' => false], $row->refresh()->notification_preferences);
        Event::assertDispatchedTimes(TicketWatcherAdded::class, 1);
        $this->assertTrue(app(TicketPolicy::class)->view($watcher, $ticket));
    }

    public function test_unwatching_removes_the_watcher_and_its_visibility(): void
    {
        Event::fake([TicketWatcherRemoved::class]);
        $ticket = $this->ticket();
        $watcher = $this->user();
        app(ManagesWatchers::class)->watch($ticket, $watcher);

        $this->assertTrue(app(ManagesWatchers::class)->unwatch($ticket, $watcher));
        $this->assertFalse(app(ManagesWatchers::class)->unwatch($ticket, $watcher));

        $this->assertFalse(app(TicketPolicy::class)->view($watcher, $ticket));
        Event::assertDispatchedTimes(TicketWatcherRemoved::class, 1);
        $this->assertSame(
            ['ticket.created', 'ticket.watcher_added', 'ticket.watcher_removed'],
            $ticket->auditRecords()->orderBy('id')->pluck('event_name')->all(),
        );
    }

    public function test_only_staff_can_start_watching_through_the_api(): void
    {
        $ticket = $this->ticket();

        $this->actingAs($this->user())
            ->postJson('/api/ticketing/tickets/' . $ticket->getKey() . '/watchers')
            ->assertForbidden();

        $staff = $this->user(['ticket.view_any']);

        $this->actingAs($staff)
            ->postJson('/api/ticketing/tickets/' . $ticket->getKey() . '/watchers', ['notification_preferences' => ['mail' => true]])
            ->assertCreated();

        $this->actingAs($staff)
            ->deleteJson('/api/ticketing/tickets/' . $ticket->getKey() . '/watchers')
            ->assertNoContent();

        $this->assertSame(0, $ticket->watchers()->count());
    }

    protected function ticket(): Ticket
    {
        return app(CreateTicketAction::class)->create(['subject' => 'Help'], $this->user());
    }
}
