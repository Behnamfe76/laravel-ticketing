<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Listeners;

use Fereydooni\LaravelTicketing\Contracts\Notifications\ResolvesNotificationRecipients;
use Fereydooni\LaravelTicketing\Models\ConversationEntry;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Notifications\TicketCreatedNotification;
use Illuminate\Support\Facades\Notification;

class DispatchTicketNotifications
{
    public function __construct(protected ResolvesNotificationRecipients $recipients)
    {
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

        Notification::send($this->recipients->forTicketCreated($ticket), new TicketCreatedNotification($ticket));
    }
}
