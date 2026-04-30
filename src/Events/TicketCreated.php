<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Events;

use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketCreated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public readonly Ticket $ticket,
        public readonly array $context = [],
    ) {
    }
}
