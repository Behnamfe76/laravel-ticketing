<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    protected $table = 'ticketing_statuses';

    protected $guarded = [];

    protected $casts = [
        'is_default' => 'boolean',
        'is_terminal' => 'boolean',
        'transitions' => 'array',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
