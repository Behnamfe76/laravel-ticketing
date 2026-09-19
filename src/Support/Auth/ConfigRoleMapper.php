<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Support\Auth;

use Fereydooni\LaravelTicketing\Contracts\Auth\MapsTicketRoles;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Default ability mapper. Deny unless something grants the ability.
 *
 * An ability is granted when:
 *
 * 1. the actor's `hasTicketingAbility()` grants it (the host's staff roles), or
 * 2. it is one of the `requester` role abilities in `ticketing-permissions.roles.requester`,
 *    and, for a ticket-bound ability, the actor takes part in that ticket (requester,
 *    creator, or watcher).
 *
 * Any authenticated actor may therefore open a ticket and follow up on their own, but never
 * read or reply to someone else's.
 */
class ConfigRoleMapper implements MapsTicketRoles
{
    public function allows(Authenticatable $actor, string $ability, ?Ticket $ticket = null): bool
    {
        if (method_exists($actor, 'hasTicketingAbility') && $actor->hasTicketingAbility($ability, $ticket)) {
            return true;
        }

        if (! in_array($ability, $this->requesterAbilities(), true)) {
            return false;
        }

        if ($ticket === null) {
            return $ability === 'ticket.create';
        }

        return $ticket->isVisibleTo($actor);
    }

    /**
     * @return array<int, string>
     */
    protected function requesterAbilities(): array
    {
        return (array) config('ticketing-permissions.roles.requester', ['ticket.create', 'ticket.view', 'ticket.reply']);
    }
}
