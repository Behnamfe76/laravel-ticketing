<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\Mail;

use Fereydooni\LaravelTicketing\Models\EmailThread;

interface ProcessesInboundTicketMail
{
    /**
     * @param array<string, mixed> $message
     */
    public function process(array $message): EmailThread;
}
