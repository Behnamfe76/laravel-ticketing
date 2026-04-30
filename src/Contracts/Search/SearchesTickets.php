<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\Search;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

interface SearchesTickets
{
    /**
     * @param array<string, mixed> $filters
     * @return Collection<int, \Fereydooni\LaravelTicketing\Models\Ticket>
     */
    public function search(array $filters = [], ?Authenticatable $actor = null): Collection;
}
