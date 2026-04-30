<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\Search;

use Fereydooni\LaravelTicketing\Models\SavedView;
use Illuminate\Contracts\Auth\Authenticatable;

interface BuildsSavedViews
{
    /**
     * @param array<string, mixed> $filters
     */
    public function create(string $name, array $filters, ?Authenticatable $owner = null): SavedView;
}
