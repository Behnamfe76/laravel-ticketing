<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Http\Resources\Api;

use Fereydooni\LaravelTicketing\Models\Attachment;
use Fereydooni\LaravelTicketing\Models\ConversationEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

/**
 * @mixin \Fereydooni\LaravelTicketing\Models\Ticket
 */
class TicketResource extends JsonResource
{
    /**
     * The conversation and attachments are included only when loaded. Internal notes and
     * staff-only attachments are included only for actors allowed to write internal notes.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $seesStaffContent = $request->user() !== null
            && Gate::forUser($request->user())->allows('note', $this->resource);

        return [
            'id' => $this->id,
            'number' => $this->number,
            'subject' => $this->subject,
            'description' => $this->description,
            'status_id' => $this->status_id,
            'priority_id' => $this->priority_id,
            'category_id' => $this->category_id,
            'type_id' => $this->type_id,
            'source' => $this->source,
            'resolved_at' => $this->resolved_at?->toISOString(),
            'closed_at' => $this->closed_at?->toISOString(),
            'last_activity_at' => $this->last_activity_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'attachments' => $this->whenLoaded('attachments', fn () => $this->attachmentsFor($this->attachments, $seesStaffContent, $request)),
            'conversation' => $this->whenLoaded('conversationEntries', fn () => $this->conversationEntries
                ->filter(fn (ConversationEntry $entry): bool => $seesStaffContent || ! $entry->is_internal)
                ->sortBy('id')
                ->values()
                ->map(fn (ConversationEntry $entry): array => [
                    'id' => $entry->id,
                    'entry_type' => $entry->entry_type,
                    'body' => $entry->body,
                    'body_format' => $entry->body_format,
                    'author_type' => $entry->author_type,
                    'author_id' => $entry->author_id,
                    'created_at' => $entry->created_at?->toISOString(),
                    'attachments' => $entry->relationLoaded('attachments')
                        ? $this->attachmentsFor($entry->attachments, $seesStaffContent, $request)
                        : [],
                ])),
        ];
    }

    /**
     * @param iterable<int, Attachment> $attachments
     * @return array<int, array<string, mixed>>
     */
    protected function attachmentsFor(iterable $attachments, bool $seesStaffContent, Request $request): array
    {
        return collect($attachments)
            ->filter(fn (Attachment $attachment): bool => $seesStaffContent || $attachment->visibility_scope !== 'staff')
            ->map(fn (Attachment $attachment): array => [
                'id' => $attachment->id,
                'original_name' => $attachment->original_name,
                'mime_type' => $attachment->mime_type,
                'size_bytes' => $attachment->size_bytes,
                'url' => $this->downloadUrl($attachment, $request),
            ])
            ->values()
            ->all();
    }

    protected function downloadUrl(Attachment $attachment, Request $request): ?string
    {
        // Link through the same adapter (portal, staff, or API) that is serving this response.
        if (preg_match('/^ticketing\.(portal|staff|api)\./', (string) $request->route()?->getName(), $match) !== 1) {
            return null;
        }

        $name = "ticketing.{$match[1]}.tickets.attachments.show";

        return Route::has($name)
            ? route($name, ['ticket' => $this->id, 'attachment' => $attachment->id])
            : null;
    }
}
