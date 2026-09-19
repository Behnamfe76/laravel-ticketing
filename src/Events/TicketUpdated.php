<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Events;

use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Ticket fields changed. `$changes` maps each changed attribute to its new value.
 */
class TicketUpdated implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param array<string, mixed> $changes
     */
    public function __construct(
        public readonly Ticket $ticket,
        public readonly array $changes,
        public readonly ?Authenticatable $actor = null,
    ) {
    }
}
