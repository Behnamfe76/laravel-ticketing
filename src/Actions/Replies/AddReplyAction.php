<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Actions\Replies;

use Fereydooni\LaravelTicketing\Contracts\MultiTenancy\ResolvesTenantContext;
use Fereydooni\LaravelTicketing\Contracts\Tickets\AddsTicketReplies;
use Fereydooni\LaravelTicketing\Events\TicketReplyAdded;
use Fereydooni\LaravelTicketing\Listeners\RecordTicketAuditTrail;
use Fereydooni\LaravelTicketing\Models\ConversationEntry;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Services\Attachments\AttachmentManager;
use Fereydooni\LaravelTicketing\Support\Auth\ActorType;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AddReplyAction implements AddsTicketReplies
{
    public function __construct(
        protected ResolvesTenantContext $tenantContext,
        protected AttachmentManager $attachments,
    ) {
    }

    public function add(Ticket $ticket, array $attributes, ?Authenticatable $actor = null): ConversationEntry
    {
        if (blank($attributes['body'] ?? null)) {
            throw new InvalidArgumentException('Reply body is required.');
        }

        return DB::transaction(function () use ($ticket, $attributes, $actor): ConversationEntry {
            $entryType = $attributes['entry_type'] ?? 'public_reply';

            $entry = $ticket->conversationEntries()->create([
                'tenant_id' => $this->tenantContext->id() ?? $ticket->tenant_id,
                'author_type' => ActorType::of($actor),
                'author_id' => $actor?->getAuthIdentifier(),
                'entry_type' => $entryType,
                'body' => $attributes['body'],
                'body_format' => $attributes['body_format'] ?? 'plain',
                'source' => $attributes['source'] ?? 'workflow',
                'visibility_scope' => $entryType === 'internal_note' ? 'staff' : 'public',
                'meta' => ['mentions' => $attributes['mentions'] ?? []],
            ]);

            // `attachments` is trusted metadata from host code; `uploads` are UploadedFile
            // instances and are stored by the package.
            foreach ((array) ($attributes['attachments'] ?? []) as $attachment) {
                $this->attachments->attach($entry, (array) $attachment);
            }

            foreach ((array) ($attributes['uploads'] ?? []) as $upload) {
                $this->attachments->store($entry, $upload, $actor, $entry->visibility_scope);
            }

            $ticket->forceFill(['last_activity_at' => now()])->save();

            app(RecordTicketAuditTrail::class)->replyAdded($ticket, $entry, $actor);
            TicketReplyAdded::dispatch($ticket, $entry, $actor);

            return $entry->refresh();
        });
    }
}
