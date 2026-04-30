<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ConversationEntry extends Model
{
    protected $table = 'ticketing_conversation_entries';

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];

    public function getIsInternalAttribute(): bool
    {
        return $this->entry_type === 'internal_note';
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function author(): MorphTo
    {
        return $this->morphTo();
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'in_reply_to_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
