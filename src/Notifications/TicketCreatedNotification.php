<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Notifications;

use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Ticket $ticket)
    {
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
            ->subject('Ticket ' . $this->ticket->number . ' created')
            ->line($this->ticket->subject);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->getKey(),
            'number' => $this->ticket->number,
            'subject' => $this->ticket->subject,
        ];
    }
}
