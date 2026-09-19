<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\Tickets;

use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;

interface TransitionsTickets
{
    public function resolve(Ticket $ticket, ?Authenticatable $actor = null): Ticket;

    public function reopen(Ticket $ticket, ?Authenticatable $actor = null): Ticket;
}
