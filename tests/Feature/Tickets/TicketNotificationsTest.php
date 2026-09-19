<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Feature\Tickets;

use Fereydooni\LaravelTicketing\Actions\Replies\AddReplyAction;
use Fereydooni\LaravelTicketing\Actions\Tickets\CreateTicketAction;
use Fereydooni\LaravelTicketing\Listeners\DispatchTicketNotifications;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Notifications\TicketCreatedNotification;
use Fereydooni\LaravelTicketing\Notifications\TicketReplyAddedNotification;
use Fereydooni\LaravelTicketing\Tests\Concerns\SetsUpTicketingDatabase;
use Fereydooni\LaravelTicketing\Tests\Fixtures\TicketingUser;
use Fereydooni\LaravelTicketing\Tests\TestCase;
use Illuminate\Support\Facades\Notification;

class TicketNotificationsTest extends TestCase
{
    use SetsUpTicketingDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateTicketingDatabase();

        Status::query()->create(['name' => 'Open', 'slug' => 'open', 'is_default' => true]);
        Notification::fake();
    }

    public function test_a_new_ticket_notifies_watchers_but_not_its_creator(): void
    {
        $requester = $this->user();
        $watcher = $this->user();

        $ticket = app(CreateTicketAction::class)->create(['subject' => 'Help'], $requester);
        $this->watch($ticket, $watcher);
        app(DispatchTicketNotifications::class)->ticketCreated($ticket);

        Notification::assertSentTo($watcher, TicketCreatedNotification::class);
        Notification::assertNotSentTo($requester, TicketCreatedNotification::class);
    }

    public function test_a_staff_reply_notifies_the_requester_with_a_reply_notification(): void
    {
        $requester = $this->user();
        $staff = $this->user();
        $ticket = app(CreateTicketAction::class)->create(['subject' => 'Help'], $requester);

        app(AddReplyAction::class)->add($ticket, ['body' => 'On it'], $staff);

        Notification::assertSentTo(
            $requester,
            TicketReplyAddedNotification::class,
            fn (TicketReplyAddedNotification $notification): bool => $notification->entry->body === 'On it',
        );
        Notification::assertNotSentTo($staff, TicketReplyAddedNotification::class);
        Notification::assertNotSentTo($requester, TicketCreatedNotification::class);
    }

    public function test_the_author_of_a_reply_is_not_notified_even_when_watching(): void
    {
        $requester = $this->user();
        $ticket = app(CreateTicketAction::class)->create(['subject' => 'Help'], $requester);
        $this->watch($ticket, $requester);

        app(AddReplyAction::class)->add($ticket, ['body' => 'Any update?'], $requester);

        Notification::assertNothingSent();
    }

    public function test_the_requester_is_notified_once_when_also_a_watcher(): void
    {
        $requester = $this->user();
        $staff = $this->user();
        $ticket = app(CreateTicketAction::class)->create(['subject' => 'Help'], $requester);
        $this->watch($ticket, $requester);

        app(AddReplyAction::class)->add($ticket, ['body' => 'On it'], $staff);

        Notification::assertSentToTimes($requester, TicketReplyAddedNotification::class, 1);
    }

    public function test_internal_notes_notify_nobody(): void
    {
        $requester = $this->user();
        $ticket = app(CreateTicketAction::class)->create(['subject' => 'Help'], $requester);

        app(AddReplyAction::class)->add($ticket, ['body' => 'Staff only', 'entry_type' => 'internal_note'], $this->user());

        Notification::assertNothingSent();
    }

    public function test_channels_come_from_config(): void
    {
        config()->set('ticketing.notifications.channels', ['database']);
        $ticket = app(CreateTicketAction::class)->create(['subject' => 'Help'], $this->user());

        $this->assertSame(['database'], (new TicketCreatedNotification($ticket))->via($this->user()));
    }

    protected function watch(Ticket $ticket, TicketingUser $actor): void
    {
        $ticket->watchers()->create([
            'actor_type' => $actor->getMorphClass(),
            'actor_id' => $actor->getKey(),
            'relation_type' => 'watcher',
        ]);
    }
}
