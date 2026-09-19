<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Actions\Tickets;

use Fereydooni\LaravelTicketing\Contracts\Tickets\TransitionsTickets;
use Fereydooni\LaravelTicketing\Contracts\Tickets\UpdatesTickets;
use Fereydooni\LaravelTicketing\Events\TicketUpdated;
use Fereydooni\LaravelTicketing\Listeners\RecordTicketAuditTrail;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Support\Tickets\TaxonomyReferences;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Updates a ticket's editable fields.
 *
 * Only {@see self::EDITABLE} are written; anything else in `$attributes` is ignored. A
 * `status_id` is handed to {@see TransitionsTickets::changeStatus()} so the workflow rules,
 * audit record, and `TicketStatusChanged` event apply however the status is changed.
 */
class UpdateTicketAction implements UpdatesTickets
{
    public const EDITABLE = ['subject', 'description', 'priority_id', 'category_id', 'type_id', 'meta'];

    public function __construct(
        protected TaxonomyReferences $taxonomy,
        protected TransitionsTickets $transitions,
    ) {
    }

    public function update(Ticket $ticket, array $attributes, ?Authenticatable $actor = null): Ticket
    {
        $this->taxonomy->assertExist($attributes);

        return DB::transaction(function () use ($ticket, $attributes, $actor): Ticket {
            $ticket->fill(Arr::only($attributes, self::EDITABLE));
            $changes = Arr::except($ticket->getDirty(), ['updated_at']);

            if ($changes !== []) {
                $ticket->forceFill(['last_activity_at' => now()])->save();

                app(RecordTicketAuditTrail::class)->ticketUpdated($ticket, $changes, $actor);
                TicketUpdated::dispatch($ticket, $changes, $actor);
            }

            if (array_key_exists('status_id', $attributes) && $attributes['status_id'] !== null) {
                $ticket = $this->transitions->changeStatus($ticket, $attributes['status_id'], $actor);
            }

            return $ticket->refresh();
        });
    }
}
