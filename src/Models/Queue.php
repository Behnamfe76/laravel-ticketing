<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Models;

use Fereydooni\LaravelTicketing\Models\Concerns\BelongsToTicketingTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Queue extends Model
{
    use BelongsToTicketingTenant;

    protected $table = 'ticketing_queues';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function defaultAssignee(): MorphTo
    {
        return $this->morphTo('default_assignee');
    }
}
