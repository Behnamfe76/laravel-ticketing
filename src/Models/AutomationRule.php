<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Models;

use Fereydooni\LaravelTicketing\Models\Concerns\BelongsToTicketingTenant;
use Illuminate\Database\Eloquent\Model;

class AutomationRule extends Model
{
    use BelongsToTicketingTenant;

    protected $table = 'ticketing_automation_rules';

    protected $guarded = [];

    protected $casts = [
        'conditions' => 'array',
        'actions' => 'array',
        'is_active' => 'boolean',
        'stop_processing' => 'boolean',
    ];
}
