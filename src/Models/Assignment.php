<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Models;

use Fereydooni\LaravelTicketing\Models\Concerns\BelongsToTicketingTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Assignment extends Model
{
    use BelongsToTicketingTenant;

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

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }
}
