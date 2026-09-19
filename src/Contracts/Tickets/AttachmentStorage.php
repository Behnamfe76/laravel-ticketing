<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\Tickets;

use Fereydooni\LaravelTicketing\Models\Attachment;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

interface AttachmentStorage extends ResolvesAttachmentStorage
{
    /**
     * Record an attachment from trusted metadata.
     *
     * The `disk` and `path` are stored as given, so this is for host code that has already
     * stored the file. Never pass user input here; use {@see self::store()} for uploads.
     *
     * @param array<string, mixed> $attributes
     */
    public function attach(Model $attachable, array $attributes): Attachment;

    /**
     * Store an uploaded file on the configured disk and record it as an attachment.
     *
     * @throws \Illuminate\Validation\ValidationException when the file exceeds
     *                                                    `attachments.max_upload_size_kb`
     */
    public function store(Model $attachable, UploadedFile $file, ?Authenticatable $uploader = null, string $visibilityScope = 'public'): Attachment;
}
