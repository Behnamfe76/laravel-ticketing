<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Events;

use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketCreated implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public readonly Ticket $ticket,
        public readonly array $context = [],
        public readonly ?Authenticatable $actor = null,
    ) {
    }
}
