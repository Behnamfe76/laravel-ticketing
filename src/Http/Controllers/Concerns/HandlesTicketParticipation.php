<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Http\Controllers\Concerns;

use Fereydooni\LaravelTicketing\Contracts\Tickets\ManagesWatchers;
use Fereydooni\LaravelTicketing\Models\Attachment;
use Fereydooni\LaravelTicketing\Models\ConversationEntry;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Services\Attachments\AttachmentManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Watcher and attachment endpoints shared by the HTTP adapters.
 *
 * Requires the `AuthorizesRequests` trait on the using controller.
 */
trait HandlesTicketParticipation
{
    /** Staff start following a ticket themselves. */
    public function watch(Request $request, Ticket $ticket, ManagesWatchers $watchers): JsonResponse
    {
        $this->authorize('watch', $ticket);

        $data = $request->validate(['notification_preferences' => ['nullable', 'array']]);
        $actor = $this->modelActor($request);

        return response()->json([
            'data' => $watchers->watch($ticket, $actor, 'watcher', $data['notification_preferences'] ?? null, $actor),
        ], 201);
    }

    /** Any watcher stops following a ticket. */
    public function unwatch(Request $request, Ticket $ticket, ManagesWatchers $watchers): JsonResponse
    {
        $this->authorize('view', $ticket);

        $actor = $this->modelActor($request);
        $watchers->unwatch($ticket, $actor, 'watcher', $actor);

        return response()->json(null, 204);
    }

    /**
     * Download an attachment of the ticket or of one of its conversation entries. Attachments
     * of internal notes, and staff-only attachments, require the `note` ability.
     */
    public function downloadAttachment(Ticket $ticket, Attachment $attachment, AttachmentManager $attachments): StreamedResponse
    {
        $this->authorize('view', $ticket);

        $entry = $attachment->attachable;
        $belongsToTicket = ($entry instanceof Ticket && $entry->is($ticket))
            || ($entry instanceof ConversationEntry && (string) $entry->ticket_id === (string) $ticket->getKey());

        if (! $belongsToTicket) {
            throw new NotFoundHttpException();
        }

        if ($attachment->visibility_scope === 'staff' || ($entry instanceof ConversationEntry && $entry->is_internal)) {
            $this->authorize('note', $ticket);
        }

        return $attachments->download($attachment);
    }

    protected function modelActor(Request $request): Model
    {
        $actor = $request->user();

        abort_unless($actor instanceof Model, 403);

        return $actor;
    }
}
