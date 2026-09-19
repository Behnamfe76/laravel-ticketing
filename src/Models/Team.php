<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Models;

use Fereydooni\LaravelTicketing\Models\Concerns\BelongsToTicketingTenant;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use BelongsToTicketingTenant;

    protected $table = 'ticketing_teams';

    protected $guarded = [];

    protected $casts = [
        'settings' => 'array',
    ];
}
