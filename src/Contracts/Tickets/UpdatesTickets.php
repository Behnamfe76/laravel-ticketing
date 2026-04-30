<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\Tickets;

use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;

interface UpdatesTickets
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function update(Ticket $ticket, array $attributes, ?Authenticatable $actor = null): Ticket;
}
