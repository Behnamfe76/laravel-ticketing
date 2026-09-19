<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\Tickets;

use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Models\Watcher;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * Adds and removes ticket watchers.
 *
 * Watching grants visibility of the ticket under the default role mapper, so callers must
 * authorize who may add whom. The HTTP adapters only let staff watch a ticket themselves.
 */
interface ManagesWatchers
{
    /**
     * Add the watcher, or update its notification preferences if it already exists.
     *
     * @param array<string, mixed>|null $preferences
     */
    public function watch(Ticket $ticket, Model $watcher, string $relationType = 'watcher', ?array $preferences = null, ?Authenticatable $actor = null): Watcher;

    /**
     * @return bool whether a watcher was removed
     */
    public function unwatch(Ticket $ticket, Model $watcher, string $relationType = 'watcher', ?Authenticatable $actor = null): bool;
}
