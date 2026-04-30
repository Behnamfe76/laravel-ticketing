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

        return $query->where(function (Builder $query) use ($actor): void {
            $query
                ->where(function (Builder $query) use ($actor): void {
                    $query->where('requester_type', $actor::class)
                        ->where('requester_id', $actor->getAuthIdentifier());
                })
                ->orWhere(function (Builder $query) use ($actor): void {
                    $query->where('creator_type', $actor::class)
                        ->where('creator_id', $actor->getAuthIdentifier());
                })
                ->orWhereHas('watchers', function (Builder $query) use ($actor): void {
                    $query->where('actor_type', $actor::class)
                        ->where('actor_id', $actor->getAuthIdentifier());
                });
        });
    }
}
