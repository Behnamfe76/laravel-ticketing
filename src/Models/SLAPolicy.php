<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Models;

use Fereydooni\LaravelTicketing\Models\Concerns\BelongsToTicketingTenant;
use Illuminate\Database\Eloquent\Model;

class SLAPolicy extends Model
{
    use BelongsToTicketingTenant;

    protected $table = 'ticketing_sla_policies';

    protected $guarded = [];

    protected $casts = [
        'conditions' => 'array',
        'calendar_rules' => 'array',
        'is_active' => 'boolean',
    ];
}
