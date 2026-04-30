<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $table = 'ticketing_tags';

    protected $guarded = [];

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'ticketing_tag_ticket');
    }
}
