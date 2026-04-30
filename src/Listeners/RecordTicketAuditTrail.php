<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Listeners;

use Fereydooni\LaravelTicketing\Models\Assignment;
use Fereydooni\LaravelTicketing\Models\ConversationEntry;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;

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

    /**
     * @param array<string, mixed> $context
     */
    protected function record(Ticket $ticket, string $event, object $subject, ?Authenticatable $actor = null, array $context = []): void
    {
        $ticket->auditRecords()->create([
            'tenant_id' => $ticket->tenant_id,
            'actor_type' => $actor?->getMorphClass() ?? ($actor ? $actor::class : null),
            'actor_id' => $actor?->getAuthIdentifier(),
            'event_name' => $event,
            'subject_type' => $subject::class,
            'subject_id' => method_exists($subject, 'getKey') ? $subject->getKey() : null,
            'context' => $context,
            'occurred_at' => now(),
        ]);
    }
}
