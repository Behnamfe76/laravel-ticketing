<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Services\Attachments;

use Fereydooni\LaravelTicketing\Contracts\Tickets\AttachmentStorage;
use Fereydooni\LaravelTicketing\Models\Attachment;
use Fereydooni\LaravelTicketing\Support\Auth\ActorType;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentManager implements AttachmentStorage
{
    public function disk(?string $visibilityScope = null): Filesystem
    {
        return Storage::disk($this->diskName());
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

    /**
     * The package chooses the disk, directory, and file name; nothing about the storage location
     * comes from the client. The MIME type is detected from the file contents, and the original
     * name is kept only as display metadata.
     */
    public function store(Model $attachable, UploadedFile $file, ?Authenticatable $uploader = null, string $visibilityScope = 'public'): Attachment
    {
        $maxKilobytes = (int) config('ticketing.attachments.max_upload_size_kb', 10240);

        if ($file->getSize() > $maxKilobytes * 1024) {
            throw ValidationException::withMessages([
                'files' => "Attachments may not be larger than {$maxKilobytes} kilobytes.",
            ]);
        }

        $directory = $this->directory($visibilityScope) . '/' . ($attachable->tenant_id ?? 'global') . '/' . now()->format('Y/m');
        $path = $file->store($directory, ['disk' => $this->diskName(), 'visibility' => 'private']);

        if ($path === false) {
            throw ValidationException::withMessages(['files' => 'The attachment could not be stored.']);
        }

        return $this->attach($attachable, [
            'uploaded_by_type' => ActorType::of($uploader),
            'uploaded_by_id' => $uploader?->getAuthIdentifier(),
            'disk' => $this->diskName(),
            'path' => $path,
            'original_name' => $this->displayName($file),
            'mime_type' => $file->getMimeType(),
            'size_bytes' => (int) $file->getSize(),
            'checksum' => hash_file('sha256', $file->getRealPath()) ?: null,
            'visibility_scope' => $visibilityScope,
        ]);
    }

    /**
     * Stream an attachment as a download. Authorization is the caller's responsibility.
     */
    public function download(Attachment $attachment): StreamedResponse
    {
        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }

    protected function diskName(): string
    {
        return (string) config('ticketing.attachments.disk', 'local');
    }

    protected function displayName(UploadedFile $file): string
    {
        $name = preg_replace('/[\x00-\x1F\x7F\/\\\\]/u', '', basename($file->getClientOriginalName())) ?? '';

        return Str::limit($name !== '' ? $name : $file->hashName(), 255, '');
    }
}
