<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Models;

use Fereydooni\LaravelTicketing\Models\Concerns\BelongsToTicketingTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditRecord extends Model
{
    use BelongsToTicketingTenant;

    protected $table = 'ticketing_audit_records';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'context' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
