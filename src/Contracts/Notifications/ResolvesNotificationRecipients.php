<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\Notifications;

use Fereydooni\LaravelTicketing\Models\ConversationEntry;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Support\Collection;

interface ResolvesNotificationRecipients
{
    /**
     * @return Collection<int, object>
     */
    public function forTicketCreated(Ticket $ticket): Collection;

    /**
     * Recipients of a public reply. Never called for internal notes.
     *
     * @return Collection<int, object>
     */
    public function forReplyAdded(Ticket $ticket, ConversationEntry $entry): Collection;
}
