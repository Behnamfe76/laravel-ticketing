<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Actions\Tickets;

use Fereydooni\LaravelTicketing\Actions\Assignments\AssignTicketAction;
use Fereydooni\LaravelTicketing\Contracts\MultiTenancy\ResolvesTenantContext;
use Fereydooni\LaravelTicketing\Contracts\Tickets\CreatesTickets;
use Fereydooni\LaravelTicketing\Events\TicketCreated;
use Fereydooni\LaravelTicketing\Listeners\RecordTicketAuditTrail;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Repositories\Eloquent\EloquentTicketRepository;
use Fereydooni\LaravelTicketing\Services\Attachments\AttachmentManager;
use Fereydooni\LaravelTicketing\Support\Auth\ActorType;
use Fereydooni\LaravelTicketing\Support\Tickets\TaxonomyReferences;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CreateTicketAction implements CreatesTickets
{
    public function __construct(
        protected EloquentTicketRepository $tickets,
        protected ResolvesTenantContext $tenantContext,
        protected AttachmentManager $attachments,
        protected AssignTicketAction $assignments,
        protected TaxonomyReferences $taxonomy,
    ) {
    }

    public function create(array $attributes, ?Authenticatable $actor = null): Ticket
    {
        if (blank($attributes['subject'] ?? null)) {
            throw new InvalidArgumentException('Ticket subject is required.');
        }

        $this->taxonomy->assertExist($attributes);

        return DB::transaction(function () use ($attributes, $actor): Ticket {
            $ticket = $this->tickets->create([
                'tenant_id' => $this->tenantContext->id(),
                'number' => $attributes['number'] ?? $this->nextNumber(),
                'subject' => $attributes['subject'],
                'description' => $attributes['description'] ?? null,
                'requester_type' => ActorType::of($actor),
                'requester_id' => $actor?->getAuthIdentifier(),
                'creator_type' => ActorType::of($actor),
                'creator_id' => $actor?->getAuthIdentifier(),
                'status_id' => $attributes['status_id'] ?? $this->defaultStatus()?->getKey(),
                'priority_id' => $attributes['priority_id'] ?? null,
                'category_id' => $attributes['category_id'] ?? null,
                'type_id' => $attributes['type_id'] ?? null,
                'source' => $attributes['source'] ?? 'workflow',
                'visibility' => $attributes['visibility'] ?? 'private',
                'last_activity_at' => now(),
                'meta' => $attributes['meta'] ?? [],
            ]);

            // `attachments` is trusted metadata from host code; `uploads` are UploadedFile
            // instances and are stored by the package.
            foreach ((array) ($attributes['attachments'] ?? []) as $attachment) {
                $this->attachments->attach($ticket, (array) $attachment);
            }

            foreach ((array) ($attributes['uploads'] ?? []) as $upload) {
                $this->attachments->store($ticket, $upload, $actor);
            }

            if (isset($attributes['assignment']) && is_array($attributes['assignment'])) {
                $this->assignments->assign($ticket, $attributes['assignment'], $actor);
            }

            app(RecordTicketAuditTrail::class)->ticketCreated($ticket, $actor);
            TicketCreated::dispatch($ticket, ['source' => $ticket->source], $actor);

            return $ticket->refresh();
        });
    }

    protected function defaultStatus(): ?Status
    {
        return Status::query()->where('is_default', true)->first()
            ?? Status::query()->orderBy('sort_order')->first();
    }

    protected function nextNumber(): string
    {
        return 'TKT-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
    }
}
