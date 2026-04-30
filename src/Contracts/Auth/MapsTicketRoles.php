<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\Auth;

use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;

interface MapsTicketRoles
{
    public function allows(Authenticatable $actor, string $ability, ?Ticket $ticket = null): bool;
}
