<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Models;

use Illuminate\Database\Eloquent\Model;

class SLAPolicy extends Model
{
    protected $table = 'ticketing_sla_policies';

    protected $guarded = [];

    protected $casts = [
        'conditions' => 'array',
        'calendar_rules' => 'array',
        'is_active' => 'boolean',
    ];
}
