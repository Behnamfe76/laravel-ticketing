<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Events;

use Fereydooni\LaravelTicketing\Models\ConversationEntry;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A public reply or internal note was added. Check `$entry->entry_type` to tell them apart.
 */
class TicketReplyAdded implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly ConversationEntry $entry,
        public readonly ?Authenticatable $actor = null,
    ) {
    }
}
