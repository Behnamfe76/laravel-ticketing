<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\Tickets;

use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;

interface TransitionsTickets
{
    public function resolve(Ticket $ticket, ?Authenticatable $actor = null): Ticket;

    public function reopen(Ticket $ticket, ?Authenticatable $actor = null): Ticket;

    /**
     * Move the ticket to another workflow status.
     *
     * @throws \Illuminate\Validation\ValidationException when the status does not exist in the
     *                                                    current tenant or the current status
     *                                                    does not allow the transition
     */
    public function changeStatus(Ticket $ticket, int|string $statusId, ?Authenticatable $actor = null): Ticket;
}
