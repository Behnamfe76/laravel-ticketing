<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Support\Notifications;

use Fereydooni\LaravelTicketing\Contracts\Notifications\ResolvesNotificationRecipients;
use Fereydooni\LaravelTicketing\Models\ConversationEntry;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Default recipients: the requester plus every watcher, each once, never the actor who caused
 * the notification (the ticket's creator, or the reply's author).
 */
class ConfigNotificationRecipientResolver implements ResolvesNotificationRecipients
{
    public function forTicketCreated(Ticket $ticket): Collection
    {
        return $this->participants($ticket)
            ->reject(fn (object $recipient): bool => $this->is($recipient, $ticket->creator_type, $ticket->creator_id))
            ->values();
    }

    public function forReplyAdded(Ticket $ticket, ConversationEntry $entry): Collection
    {
        return $this->participants($ticket)
            ->reject(fn (object $recipient): bool => $this->is($recipient, $entry->author_type, $entry->author_id))
            ->values();
    }

    /**
     * @return Collection<int, object>
     */
    protected function participants(Ticket $ticket): Collection
    {
        $watchers = $ticket->watchers()
            ->with('actor')
            ->get()
            ->pluck('actor');

        return collect([$ticket->requester])
            ->merge($watchers)
            ->filter()
            ->unique(fn (object $recipient): string => $recipient instanceof Model
                ? $recipient->getMorphClass() . ':' . $recipient->getKey()
                : spl_object_hash($recipient));
    }

    protected function is(object $recipient, ?string $type, int|string|null $id): bool
    {
        return $recipient instanceof Model
            && $type !== null
            && $recipient->getMorphClass() === $type
            && (string) $recipient->getKey() === (string) $id;
    }
}
