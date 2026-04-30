<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Services\Email;

use Fereydooni\LaravelTicketing\Actions\Replies\AddReplyAction;
use Fereydooni\LaravelTicketing\Actions\Tickets\CreateTicketAction;
use Fereydooni\LaravelTicketing\Contracts\Mail\BuildsOutboundTicketMail;
use Fereydooni\LaravelTicketing\Contracts\Mail\ProcessesInboundTicketMail;
use Fereydooni\LaravelTicketing\Contracts\MultiTenancy\ResolvesTenantContext;
use Fereydooni\LaravelTicketing\Models\ConversationEntry;
use Fereydooni\LaravelTicketing\Models\EmailThread;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InboundTicketMailProcessor implements ProcessesInboundTicketMail, BuildsOutboundTicketMail
{
    public function __construct(
        protected CreateTicketAction $tickets,
        protected AddReplyAction $replies,
        protected ResolvesTenantContext $tenantContext,
    ) {
    }

    public function process(array $message): EmailThread
    {
        foreach (['message_id', 'from', 'subject', 'body'] as $required) {
            if (blank($message[$required] ?? null)) {
                throw new InvalidArgumentException('Inbound mail requires ' . $required . '.');
            }
        }

        return DB::transaction(function () use ($message): EmailThread {
            $ticket = $this->findThreadTicket($message);

            if ($ticket === null) {
                $ticket = $this->tickets->create([
                    'subject' => $message['subject'],
                    'description' => $message['body'],
                    'source' => 'email',
                    'meta' => ['sender_address' => $message['from']],
                ]);
            } else {
                $this->replies->add($ticket, [
                    'body' => $message['body'],
                    'entry_type' => 'public_reply',
                    'source' => 'email',
                ]);
            }

            return EmailThread::query()->create([
                'tenant_id' => $this->tenantContext->id() ?? $ticket->tenant_id,
                'ticket_id' => $ticket->getKey(),
                'message_id' => $message['message_id'],
                'in_reply_to' => $message['in_reply_to'] ?? null,
                'references' => $message['references'] ?? [],
                'direction' => 'inbound',
                'sender_address' => $message['from'],
                'recipient_addresses' => (array) ($message['to'] ?? []),
                'processed_at' => now(),
                'status' => 'processed',
                'meta' => ['mailbox' => $message['mailbox'] ?? config('ticketing.mail.default_mailbox')],
            ]);
        });
    }

    public function recordOutbound(ConversationEntry $entry, string $messageId): EmailThread
    {
        return EmailThread::query()->create([
            'tenant_id' => $entry->tenant_id,
            'ticket_id' => $entry->ticket_id,
            'message_id' => $messageId,
            'direction' => 'outbound',
            'sender_address' => (string) config('ticketing.mail.from.address', 'support@example.test'),
            'recipient_addresses' => [],
            'processed_at' => now(),
            'status' => 'sent',
            'meta' => ['conversation_entry_id' => $entry->getKey()],
        ]);
    }

    /**
     * @param array<string, mixed> $message
     */
    protected function findThreadTicket(array $message): ?Ticket
    {
        $messageIds = array_filter(array_merge(
            [$message['in_reply_to'] ?? null],
            (array) ($message['references'] ?? [])
        ));

        if ($messageIds === []) {
            return null;
        }

        return EmailThread::query()
            ->whereIn('message_id', $messageIds)
            ->latest()
            ->first()
            ?->ticket;
    }
}
