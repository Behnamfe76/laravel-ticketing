<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\Mail;

use Fereydooni\LaravelTicketing\Models\ConversationEntry;
use Fereydooni\LaravelTicketing\Models\EmailThread;

interface BuildsOutboundTicketMail
{
    public function recordOutbound(ConversationEntry $entry, string $messageId): EmailThread;
}
