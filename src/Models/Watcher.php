<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Models;

use Fereydooni\LaravelTicketing\Models\Concerns\BelongsToTicketingTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Watcher extends Model
{
    use BelongsToTicketingTenant;

    protected $table = 'ticketing_watchers';

    protected $guarded = [];

    protected $casts = [
        'notification_preferences' => 'array',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }
}
