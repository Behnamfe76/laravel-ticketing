<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Events;

use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * An actor stopped watching the ticket. The watcher row is already deleted, so its actor is passed by type and id.
 */
class TicketWatcherRemoved implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly string $watcherType,
        public readonly int|string $watcherId,
        public readonly string $relationType,
        public readonly ?Authenticatable $actor = null,
    ) {
    }
}
