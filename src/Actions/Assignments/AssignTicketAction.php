<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Actions\Assignments;

use Fereydooni\LaravelTicketing\Contracts\MultiTenancy\ResolvesTenantContext;
use Fereydooni\LaravelTicketing\Contracts\Tickets\AssignsTickets;
use Fereydooni\LaravelTicketing\Events\TicketAssigned;
use Fereydooni\LaravelTicketing\Listeners\RecordTicketAuditTrail;
use Fereydooni\LaravelTicketing\Models\Assignment;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Fereydooni\LaravelTicketing\Support\Auth\ActorType;

class AssignTicketAction implements AssignsTickets
{
    public function __construct(protected ResolvesTenantContext $tenantContext)
    {
    }

    public function assign(Ticket $ticket, array $target, ?Authenticatable $actor = null): Assignment
    {
        if (blank($target['target_type'] ?? null) || blank($target['target_id'] ?? null)) {
            throw new InvalidArgumentException('Assignment target_type and target_id are required.');
        }

        return DB::transaction(function () use ($ticket, $target, $actor): Assignment {
            $ticket->assignments()->current()->update([
                'is_current' => false,
                'released_at' => now(),
            ]);

            $assignment = $ticket->assignments()->create([
                'tenant_id' => $this->tenantContext->id() ?? $ticket->tenant_id,
                'target_type' => $target['target_type'],
                'target_id' => (string) $target['target_id'],
                'assigned_by_type' => ActorType::of($actor),
                'assigned_by_id' => $actor?->getAuthIdentifier(),
                'reason' => $target['reason'] ?? null,
                'is_current' => true,
                'assigned_at' => now(),
                'meta' => $target['meta'] ?? [],
            ]);

            $ticket->forceFill([
                'current_assignment_id' => $assignment->getKey(),
                'last_activity_at' => now(),
            ])->save();

            app(RecordTicketAuditTrail::class)->ticketAssigned($ticket, $assignment, $actor);
            TicketAssigned::dispatch($ticket, $assignment, $actor);

            return $assignment->refresh();
        });
    }
}
