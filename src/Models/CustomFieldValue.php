<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Models;

use Fereydooni\LaravelTicketing\Models\Concerns\BelongsToTicketingTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomFieldValue extends Model
{
    use BelongsToTicketingTenant;

    protected $table = 'ticketing_custom_field_values';

    protected $guarded = [];

    protected $casts = [
        'value' => 'array',
    ];

    public function definition(): BelongsTo
    {
        return $this->belongsTo(CustomFieldDefinition::class, 'field_definition_id');
    }

    public function valuable(): MorphTo
    {
        return $this->morphTo();
    }
}
