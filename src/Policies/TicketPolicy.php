<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Policies;

use Fereydooni\LaravelTicketing\Contracts\Auth\MapsTicketRoles;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;

class TicketPolicy
{
    public function __construct(protected MapsTicketRoles $roles)
    {
    }

    public function create(Authenticatable $actor): bool
    {
        return $this->roles->allows($actor, 'ticket.create');
    }

    public function view(Authenticatable $actor, Ticket $ticket): bool
    {
        return $this->roles->allows($actor, 'ticket.view', $ticket);
    }

    public function reply(Authenticatable $actor, Ticket $ticket): bool
    {
        return $this->roles->allows($actor, 'ticket.reply', $ticket);
    }

    public function note(Authenticatable $actor, Ticket $ticket): bool
    {
        return $this->roles->allows($actor, 'ticket.note', $ticket);
    }

    public function assign(Authenticatable $actor, Ticket $ticket): bool
    {
        return $this->roles->allows($actor, 'ticket.assign', $ticket);
    }

    public function manage(Authenticatable $actor, Ticket $ticket): bool
    {
        return $this->roles->allows($actor, 'ticket.manage', $ticket);
    }
}
