<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\Tickets;

use Fereydooni\LaravelTicketing\Models\Assignment;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;

interface AssignsTickets
{
    /**
     * @param array<string, mixed> $target
     */
    public function assign(Ticket $ticket, array $target, ?Authenticatable $actor = null): Assignment;
}
