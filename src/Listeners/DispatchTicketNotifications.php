<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Listeners;

use Fereydooni\LaravelTicketing\Contracts\Notifications\ResolvesNotificationRecipients;
use Fereydooni\LaravelTicketing\Events\TicketCreated;
use Fereydooni\LaravelTicketing\Events\TicketReplyAdded;
use Fereydooni\LaravelTicketing\Models\ConversationEntry;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Notifications\TicketCreatedNotification;
use Fereydooni\LaravelTicketing\Notifications\TicketReplyAddedNotification;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Notification;

/**
 * Built-in ticket notifications.
 *
 * Subscribed only while `ticketing.features.notifications` is true. Hosts that deliver
 * notifications through their own pipeline turn that switch off and listen to the package
 * events (`TicketCreated`, `TicketReplyAdded`, ...) themselves.
 */
class DispatchTicketNotifications
{
    public function __construct(protected ResolvesNotificationRecipients $recipients)
    {
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(TicketCreated::class, fn (TicketCreated $event) => $this->ticketCreated($event->ticket));
        $events->listen(TicketReplyAdded::class, fn (TicketReplyAdded $event) => $this->replyAdded($event->ticket, $event->entry));
    }

    public function ticketCreated(Ticket $ticket): void
    {
        Notification::send($this->recipients->forTicketCreated($ticket), new TicketCreatedNotification($ticket));
    }

    public function replyAdded(Ticket $ticket, ConversationEntry $entry): void
    {
        if ($entry->entry_type === 'internal_note') {
            return;
        }

        Notification::send($this->recipients->forReplyAdded($ticket, $entry), new TicketReplyAddedNotification($ticket, $entry));
    }
}
