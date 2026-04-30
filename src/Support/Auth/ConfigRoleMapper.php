<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Support\Auth;

use Fereydooni\LaravelTicketing\Contracts\Auth\MapsTicketRoles;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;

class ConfigRoleMapper implements MapsTicketRoles
{
    public function allows(Authenticatable $actor, string $ability, ?Ticket $ticket = null): bool
    {
        if (method_exists($actor, 'hasTicketingAbility') && $actor->hasTicketingAbility($ability, $ticket)) {
            return true;
        }

        if ($ticket !== null && $this->ownsTicket($actor, $ticket)) {
            return in_array($ability, ['ticket.view', 'ticket.reply'], true);
        }

        return in_array($ability, ['ticket.create', 'ticket.view', 'ticket.reply'], true);
    }

    protected function ownsTicket(Authenticatable $actor, Ticket $ticket): bool
    {
        return $ticket->requester_type === $actor::class && (string) $ticket->requester_id === (string) $actor->getAuthIdentifier();
    }
}
