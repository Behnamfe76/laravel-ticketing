<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $table = 'ticketing_teams';

    protected $guarded = [];

    protected $casts = [
        'settings' => 'array',
    ];
}
