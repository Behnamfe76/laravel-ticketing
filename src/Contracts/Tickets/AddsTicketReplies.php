<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\Tickets;

use Fereydooni\LaravelTicketing\Models\ConversationEntry;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;

interface AddsTicketReplies
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function add(Ticket $ticket, array $attributes, ?Authenticatable $actor = null): ConversationEntry;
}
