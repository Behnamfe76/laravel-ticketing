<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Fixtures;

use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class TicketingUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $guarded = [];

    protected array $ticketingAbilities = [];

    /**
     * @param array<int, string> $abilities
     */
    public function withTicketingAbilities(array $abilities): self
    {
        $this->ticketingAbilities = $abilities;

        return $this;
    }

    public function hasTicketingAbility(string $ability, ?Ticket $ticket = null): bool
    {
        return in_array($ability, $this->ticketingAbilities, true);
    }
}
