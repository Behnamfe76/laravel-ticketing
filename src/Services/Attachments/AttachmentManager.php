<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Services\Attachments;

use Fereydooni\LaravelTicketing\Contracts\Tickets\AttachmentStorage;
use Fereydooni\LaravelTicketing\Models\Attachment;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AttachmentManager implements AttachmentStorage
{
    public function disk(?string $visibilityScope = null): Filesystem
    {
        return Storage::disk((string) config('ticketing.attachments.disk', 'local'));
    }

    public function directory(?string $visibilityScope = null): string
    {
        return trim((string) config('ticketing.attachments.directory', 'ticketing'), '/');
    }

    public function attach(Model $attachable, array $attributes): Attachment
    {
        Validator::make($attributes, Attachment::rules())->validate();

        return $attachable->attachments()->create([
            'tenant_id' => $attributes['tenant_id'] ?? $attachable->tenant_id ?? null,
            'uploaded_by_type' => $attributes['uploaded_by_type'] ?? null,
            'uploaded_by_id' => $attributes['uploaded_by_id'] ?? null,
            'disk' => $attributes['disk'],
            'path' => $attributes['path'],
            'original_name' => $attributes['original_name'],
            'mime_type' => $attributes['mime_type'] ?? null,
            'size_bytes' => $attributes['size_bytes'] ?? 0,
            'checksum' => $attributes['checksum'] ?? null,
            'visibility_scope' => $attributes['visibility_scope'] ?? 'private',
            'meta' => $attributes['meta'] ?? [],
        ]);
    }
}
