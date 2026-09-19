<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Models;

use Fereydooni\LaravelTicketing\Models\Concerns\BelongsToTicketingTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Priority extends Model
{
    use BelongsToTicketingTenant;

    protected $table = 'ticketing_priorities';

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
