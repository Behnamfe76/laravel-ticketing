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

    public static function rules(): array
    {
        return [
            'disk' => ['required', 'string'],
            'path' => ['required', 'string'],
            'original_name' => ['required', 'string'],
            'size_bytes' => ['integer', 'min:0'],
        ];
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploadedBy(): MorphTo
    {
        return $this->morphTo('uploaded_by');
    }
}
