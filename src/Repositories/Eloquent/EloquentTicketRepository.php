<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Repositories\Eloquent;

use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EloquentTicketRepository
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Ticket
    {
        return Ticket::query()->create($attributes);
    }

    public function find(int|string $id): ?Ticket
    {
        return Ticket::query()->find($id);
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function visibleFor(?Authenticatable $actor = null): Collection
    {
        return $this->visibleQuery($actor)->latest('last_activity_at')->get();
    }

    public function visibleQuery(?Authenticatable $actor = null): Builder
    {
        $query = Ticket::query();

        if ($actor === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->visibleTo($actor);
    }
}
