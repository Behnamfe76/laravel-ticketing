<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Repositories\Search;

use Fereydooni\LaravelTicketing\Contracts\Search\SearchesTickets;
use Fereydooni\LaravelTicketing\Models\SavedView;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentTicketSearchRepository implements SearchesTickets
{
    public function search(array $filters = [], ?Authenticatable $actor = null): Collection
    {
        return $this->query($this->mergeSavedViewFilters($filters))->get();
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function query(array $filters = []): Builder
    {
        $query = Ticket::query()->with(['status', 'priority', 'category', 'tags']);

        foreach (['status_id', 'priority_id', 'category_id', 'type_id'] as $column) {
            if (isset($filters[$column])) {
                $query->whereIn($column, (array) $filters[$column]);
            }
        }

        if (isset($filters['tenant_id'])) {
            $query->where('tenant_id', $filters['tenant_id']);
        }

        if (isset($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $query->where(fn (Builder $query) => $query
                ->where('subject', 'like', $term)
                ->orWhere('description', 'like', $term)
                ->orWhere('search_document', 'like', $term));
        }

        if (isset($filters['tag_id'])) {
            $query->whereHas('tags', fn (Builder $query) => $query->whereIn('ticketing_tags.id', (array) $filters['tag_id']));
        }

        if (isset($filters['queue_id'])) {
            $query->whereHas('assignments', fn (Builder $query) => $query
                ->where('target_type', 'queue')
                ->whereIn('target_id', array_map('strval', (array) $filters['queue_id']))
                ->where('is_current', true));
        }

        return $query->latest('last_activity_at');
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    protected function mergeSavedViewFilters(array $filters): array
    {
        if (! isset($filters['saved_view'])) {
            return $filters;
        }

        $view = SavedView::query()->whereKey($filters['saved_view'])->first();

        return array_merge($view?->filters ?? [], $filters);
    }
}
