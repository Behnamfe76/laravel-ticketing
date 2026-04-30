<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\Tickets;

use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;

interface CreatesTickets
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes, ?Authenticatable $actor = null): Ticket;
}
