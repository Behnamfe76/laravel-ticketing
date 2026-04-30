<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    protected $table = 'ticketing_attachments';

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploadedBy(): MorphTo
    {
        return $this->morphTo('uploaded_by');
    }
}
