<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Listeners;

use Fereydooni\LaravelTicketing\Models\Assignment;
use Fereydooni\LaravelTicketing\Models\ConversationEntry;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Models\Watcher;
use Fereydooni\LaravelTicketing\Support\Auth\ActorType;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class RecordTicketAuditTrail
{
    public function ticketCreated(Ticket $ticket, ?Authenticatable $actor = null): void
    {
        $this->record($ticket, 'ticket.created', $ticket, $actor);
    }

    public function replyAdded(Ticket $ticket, ConversationEntry $entry, ?Authenticatable $actor = null): void
    {
        $this->record($ticket, $entry->entry_type === 'internal_note' ? 'ticket.internal_note_added' : 'ticket.reply_added', $entry, $actor);
    }

    public function ticketAssigned(Ticket $ticket, Assignment $assignment, ?Authenticatable $actor = null): void
    {
        $this->record($ticket, 'ticket.assigned', $assignment, $actor, [
            'target_type' => $assignment->target_type,
            'target_id' => $assignment->target_id,
        ]);
    }

    public function ticketResolved(Ticket $ticket, ?Authenticatable $actor = null): void
    {
        $this->record($ticket, 'ticket.resolved', $ticket, $actor);
    }

    public function ticketReopened(Ticket $ticket, ?Authenticatable $actor = null): void
    {
        $this->record($ticket, 'ticket.reopened', $ticket, $actor);
    }

    public function statusChanged(Ticket $ticket, int|string|null $from, int|string|null $to, ?Authenticatable $actor = null): void
    {
        $this->record($ticket, 'ticket.status_changed', $ticket, $actor, [
            'from_status_id' => $from,
            'to_status_id' => $to,
        ]);
    }

    /**
     * @param array<string, mixed> $changes
     */
    public function ticketUpdated(Ticket $ticket, array $changes, ?Authenticatable $actor = null): void
    {
        $this->record($ticket, 'ticket.updated', $ticket, $actor, ['changes' => $changes]);
    }

    public function watcherAdded(Ticket $ticket, Watcher $watcher, ?Authenticatable $actor = null): void
    {
        $this->record($ticket, 'ticket.watcher_added', $watcher, $actor, [
            'actor_type' => $watcher->actor_type,
            'actor_id' => $watcher->actor_id,
            'relation_type' => $watcher->relation_type,
        ]);
    }

    public function watcherRemoved(Ticket $ticket, string $type, int|string $id, string $relationType, ?Authenticatable $actor = null): void
    {
        $this->record($ticket, 'ticket.watcher_removed', $ticket, $actor, [
            'actor_type' => $type,
            'actor_id' => $id,
            'relation_type' => $relationType,
        ]);
    }

    /**
     * @param array<string, mixed> $context
     */
    protected function record(Ticket $ticket, string $event, object $subject, ?Authenticatable $actor = null, array $context = []): void
    {
        $ticket->auditRecords()->create([
            'tenant_id' => $ticket->tenant_id,
            'actor_type' => ActorType::of($actor),
            'actor_id' => $actor?->getAuthIdentifier(),
            'event_name' => $event,
            'subject_type' => $subject instanceof Model ? $subject->getMorphClass() : $subject::class,
            'subject_id' => method_exists($subject, 'getKey') ? $subject->getKey() : null,
            'context' => $context,
            'occurred_at' => now(),
        ]);
    }
}
