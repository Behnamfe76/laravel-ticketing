<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Actions\Tickets;

use Fereydooni\LaravelTicketing\Contracts\Tickets\TransitionsTickets;
use Fereydooni\LaravelTicketing\Events\TicketReopened;
use Fereydooni\LaravelTicketing\Events\TicketResolved;
use Fereydooni\LaravelTicketing\Listeners\RecordTicketAuditTrail;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

/**
 * Resolves and reopens tickets, recording the audit trail and dispatching the lifecycle event,
 * so every entry point (staff, API, host code) behaves the same.
 */
class TransitionTicketAction implements TransitionsTickets
{
    public function resolve(Ticket $ticket, ?Authenticatable $actor = null): Ticket
    {
        return DB::transaction(function () use ($ticket, $actor): Ticket {
            $ticket->markResolved();

            app(RecordTicketAuditTrail::class)->ticketResolved($ticket, $actor);
            TicketResolved::dispatch($ticket, $actor);

            return $ticket->refresh();
        });
    }

    public function reopen(Ticket $ticket, ?Authenticatable $actor = null): Ticket
    {
        return DB::transaction(function () use ($ticket, $actor): Ticket {
            $ticket->reopen();

            app(RecordTicketAuditTrail::class)->ticketReopened($ticket, $actor);
            TicketReopened::dispatch($ticket, $actor);

            return $ticket->refresh();
        });
    }
}
