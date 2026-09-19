<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Events;

use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A resolved ticket was reopened.
 */
class TicketReopened implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly ?Authenticatable $actor = null,
    ) {
    }
}
