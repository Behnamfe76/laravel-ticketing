<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Models;

use Fereydooni\LaravelTicketing\Models\Concerns\BelongsToTicketingTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketType extends Model
{
    use BelongsToTicketingTenant;

    protected $table = 'ticketing_types';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'type_id');
    }
}
