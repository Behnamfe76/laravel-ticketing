<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Assignment extends Model
{
    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    protected $table = 'ticketing_assignments';

    protected $guarded = [];

    protected $casts = [
        'is_current' => 'boolean',
        'assigned_at' => 'datetime',
        'released_at' => 'datetime',
        'meta' => 'array',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function assignedBy(): MorphTo
    {
        return $this->morphTo('assigned_by');
    }
}
