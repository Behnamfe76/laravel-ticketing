<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\Tickets;

use Fereydooni\LaravelTicketing\Models\Attachment;
use Illuminate\Database\Eloquent\Model;

interface AttachmentStorage extends ResolvesAttachmentStorage
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function attach(Model $attachable, array $attributes): Attachment;
}
