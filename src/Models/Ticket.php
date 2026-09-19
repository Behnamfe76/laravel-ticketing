<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Models;

use Fereydooni\LaravelTicketing\Models\Concerns\BelongsToTicketingTenant;
use Fereydooni\LaravelTicketing\Support\Auth\ActorType;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use BelongsToTicketingTenant;
    use SoftDeletes;

    protected $table = 'ticketing_tickets';

    protected $guarded = [];

    protected $casts = [
        'first_response_due_at' => 'datetime',
        'resolution_due_at' => 'datetime',
        'first_responded_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'meta' => 'array',
    ];

    public function requester(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): MorphTo
    {
        return $this->morphTo();
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TicketType::class, 'type_id');
    }

    public function conversationEntries(): HasMany
    {
        return $this->hasMany(ConversationEntry::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function watchers(): HasMany
    {
        return $this->hasMany(Watcher::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function auditRecords(): HasMany
    {
        return $this->hasMany(AuditRecord::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'ticketing_tag_ticket');
    }

    public function customFieldValues(): MorphMany
    {
        return $this->morphMany(CustomFieldValue::class, 'valuable');
    }

    public function currentAssignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'current_assignment_id');
    }

    public function scopeForTenant(Builder $query, int|string|null $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Tickets the actor takes part in: as requester, creator, or watcher.
     *
     * Actors are matched by `getMorphClass()`, the same value the package writes, so hosts that
     * register a morph map see their own tickets.
     */
    public function scopeVisibleTo(Builder $query, Authenticatable $actor): Builder
    {
        $type = ActorType::of($actor);
        $id = $actor->getAuthIdentifier();

        return $query->where(function (Builder $query) use ($type, $id): void {
            $query
                ->where(fn (Builder $query) => $query->where('requester_type', $type)->where('requester_id', $id))
                ->orWhere(fn (Builder $query) => $query->where('creator_type', $type)->where('creator_id', $id))
                ->orWhereHas('watchers', fn (Builder $query) => $query->where('actor_type', $type)->where('actor_id', $id));
        });
    }

    public function isVisibleTo(Authenticatable $actor): bool
    {
        return static::query()->whereKey($this->getKey())->visibleTo($actor)->exists();
    }

    public function markResolved(): void
    {
        $this->forceFill([
            'resolved_at' => now(),
            'closed_at' => null,
            'last_activity_at' => now(),
        ])->save();
    }

    public function reopen(): void
    {
        $this->forceFill([
            'resolved_at' => null,
            'closed_at' => null,
            'last_activity_at' => now(),
        ])->save();
    }
}
