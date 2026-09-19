<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Notifications;

use Fereydooni\LaravelTicketing\Models\ConversationEntry;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReplyAddedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly ConversationEntry $entry,
    ) {
        $this->onQueue(config('ticketing.queue.queue', 'ticketing'));
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return array_values((array) config('ticketing.notifications.channels', ['database', 'mail']));
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('New reply on ticket ' . $this->ticket->number)
            ->line($this->ticket->subject)
            ->line((string) $this->entry->body);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->getKey(),
            'entry_id' => $this->entry->getKey(),
            'number' => $this->ticket->number,
            'subject' => $this->ticket->subject,
        ];
    }
}
