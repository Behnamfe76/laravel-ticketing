<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Actions\Tickets;

use Fereydooni\LaravelTicketing\Contracts\Tickets\TransitionsTickets;
use Fereydooni\LaravelTicketing\Events\TicketReopened;
use Fereydooni\LaravelTicketing\Events\TicketResolved;
use Fereydooni\LaravelTicketing\Events\TicketStatusChanged;
use Fereydooni\LaravelTicketing\Listeners\RecordTicketAuditTrail;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Moves tickets through the workflow, recording the audit trail and dispatching lifecycle
 * events, so every entry point (staff, API, host code) behaves the same.
 *
 * A status's `transitions` lists the slugs it may move to; an empty or null list allows any
 * status. Moving to a terminal status marks the ticket resolved (and closed for the `closed`
 * kind); moving to a non-terminal one clears both. Resolve and reopen are explicit actions and
 * are not restricted by `transitions`.
 */
class TransitionTicketAction implements TransitionsTickets
{
    public function resolve(Ticket $ticket, ?Authenticatable $actor = null): Ticket
    {
        return DB::transaction(function () use ($ticket, $actor): Ticket {
            $from = $ticket->status_id;
            $target = $this->resolvedStatus();

            $ticket->markResolved();

            if ($target !== null && $target->getKey() !== $from) {
                $ticket->forceFill(['status_id' => $target->getKey()])->save();
            }

            app(RecordTicketAuditTrail::class)->ticketResolved($ticket, $actor);
            TicketResolved::dispatch($ticket, $actor);
            $this->dispatchStatusChange($ticket, $from, $actor);

            return $ticket->refresh();
        });
    }

    public function reopen(Ticket $ticket, ?Authenticatable $actor = null): Ticket
    {
        return DB::transaction(function () use ($ticket, $actor): Ticket {
            $from = $ticket->status_id;
            $target = $this->openStatus();

            $ticket->reopen();

            if ($target !== null && $target->getKey() !== $from) {
                $ticket->forceFill(['status_id' => $target->getKey()])->save();
            }

            app(RecordTicketAuditTrail::class)->ticketReopened($ticket, $actor);
            TicketReopened::dispatch($ticket, $actor);
            $this->dispatchStatusChange($ticket, $from, $actor);

            return $ticket->refresh();
        });
    }

    public function changeStatus(Ticket $ticket, int|string $statusId, ?Authenticatable $actor = null): Ticket
    {
        $target = Status::query()->find($statusId);

        if ($target === null) {
            throw ValidationException::withMessages(['status_id' => 'The selected status_id is invalid.']);
        }

        if ((string) $target->getKey() === (string) $ticket->status_id) {
            return $ticket;
        }

        $current = $ticket->status;

        if ($current !== null && ! $this->allows($current, $target)) {
            throw ValidationException::withMessages([
                'status_id' => "A ticket cannot move from {$current->slug} to {$target->slug}.",
            ]);
        }

        return DB::transaction(function () use ($ticket, $target, $actor): Ticket {
            $from = $ticket->status_id;

            $ticket->forceFill([
                'status_id' => $target->getKey(),
                'resolved_at' => $target->is_terminal ? ($ticket->resolved_at ?? now()) : null,
                'closed_at' => $target->is_terminal && $target->kind === 'closed' ? now() : null,
                'last_activity_at' => now(),
            ])->save();

            app(RecordTicketAuditTrail::class)->statusChanged($ticket, $from, $target->getKey(), $actor);
            $this->dispatchStatusChange($ticket, $from, $actor);

            return $ticket->refresh();
        });
    }

    protected function allows(Status $from, Status $to): bool
    {
        $allowed = (array) ($from->transitions ?? []);

        return $allowed === [] || in_array($to->slug, $allowed, true);
    }

    protected function dispatchStatusChange(Ticket $ticket, int|string|null $from, ?Authenticatable $actor): void
    {
        if ((string) $from !== (string) $ticket->status_id) {
            TicketStatusChanged::dispatch($ticket, $from, $ticket->status_id, $actor);
        }
    }

    protected function resolvedStatus(): ?Status
    {
        return Status::query()->where('kind', 'resolved')->orderBy('sort_order')->first()
            ?? Status::query()->where('is_terminal', true)->orderBy('sort_order')->first();
    }

    protected function openStatus(): ?Status
    {
        return Status::query()->where('is_default', true)->where('is_terminal', false)->first()
            ?? Status::query()->where('is_terminal', false)->orderBy('sort_order')->first();
    }
}
