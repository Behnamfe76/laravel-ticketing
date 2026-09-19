<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Models;

use Fereydooni\LaravelTicketing\Models\Concerns\BelongsToTicketingTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailThread extends Model
{
    use BelongsToTicketingTenant;

    protected $table = 'ticketing_email_threads';

    protected $guarded = [];

    protected $casts = [
        'references' => 'array',
        'recipient_addresses' => 'array',
        'processed_at' => 'datetime',
        'meta' => 'array',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
