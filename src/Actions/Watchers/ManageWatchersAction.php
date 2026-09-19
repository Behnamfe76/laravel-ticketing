<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Actions\Watchers;

use Fereydooni\LaravelTicketing\Contracts\Tickets\ManagesWatchers;
use Fereydooni\LaravelTicketing\Events\TicketWatcherAdded;
use Fereydooni\LaravelTicketing\Events\TicketWatcherRemoved;
use Fereydooni\LaravelTicketing\Listeners\RecordTicketAuditTrail;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Models\Watcher;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ManageWatchersAction implements ManagesWatchers
{
    public function watch(Ticket $ticket, Model $watcher, string $relationType = 'watcher', ?array $preferences = null, ?Authenticatable $actor = null): Watcher
    {
        return DB::transaction(function () use ($ticket, $watcher, $relationType, $preferences, $actor): Watcher {
            $row = $ticket->watchers()->firstOrNew([
                'actor_type' => $watcher->getMorphClass(),
                'actor_id' => $watcher->getKey(),
                'relation_type' => $relationType,
            ]);

            $isNew = ! $row->exists;

            $row->fill(['tenant_id' => $ticket->tenant_id]);

            if ($preferences !== null) {
                $row->notification_preferences = $preferences;
            }

            $row->save();

            if ($isNew) {
                app(RecordTicketAuditTrail::class)->watcherAdded($ticket, $row, $actor);
                TicketWatcherAdded::dispatch($ticket, $row, $actor);
            }

            return $row;
        });
    }

    public function unwatch(Ticket $ticket, Model $watcher, string $relationType = 'watcher', ?Authenticatable $actor = null): bool
    {
        return DB::transaction(function () use ($ticket, $watcher, $relationType, $actor): bool {
            $type = $watcher->getMorphClass();
            $id = $watcher->getKey();

            $deleted = $ticket->watchers()
                ->where('actor_type', $type)
                ->where('actor_id', $id)
                ->where('relation_type', $relationType)
                ->delete() > 0;

            if ($deleted) {
                app(RecordTicketAuditTrail::class)->watcherRemoved($ticket, $type, $id, $relationType, $actor);
                TicketWatcherRemoved::dispatch($ticket, $type, $id, $relationType, $actor);
            }

            return $deleted;
        });
    }
}
