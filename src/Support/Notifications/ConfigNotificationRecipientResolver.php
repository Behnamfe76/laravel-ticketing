<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Support\Notifications;

use Fereydooni\LaravelTicketing\Contracts\Notifications\ResolvesNotificationRecipients;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Support\Collection;

class ConfigNotificationRecipientResolver implements ResolvesNotificationRecipients
{
    public function forTicketCreated(Ticket $ticket): Collection
    {
        return $ticket->watchers()
            ->with('actor')
            ->get()
            ->pluck('actor')
            ->filter()
            ->values();
    }
}
