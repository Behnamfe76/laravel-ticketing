<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SavedView extends Model
{
    protected $table = 'ticketing_saved_views';

    protected $guarded = [];

    protected $casts = [
        'filters' => 'array',
        'columns' => 'array',
        'sort' => 'array',
    ];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
