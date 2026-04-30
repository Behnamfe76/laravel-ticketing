<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Policies;

use Fereydooni\LaravelTicketing\Contracts\Auth\MapsTicketRoles;
use Illuminate\Contracts\Auth\Authenticatable;

class TicketAdministrationPolicy
{
    public function __construct(protected MapsTicketRoles $roles)
    {
    }

    public function configure(Authenticatable $actor): bool
    {
        return $this->roles->allows($actor, 'ticket.configure');
    }

    public function report(Authenticatable $actor): bool
    {
        return $this->roles->allows($actor, 'ticket.report');
    }
}
