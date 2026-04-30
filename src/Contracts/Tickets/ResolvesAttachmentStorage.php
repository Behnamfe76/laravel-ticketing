<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\Tickets;

use Illuminate\Contracts\Filesystem\Filesystem;

interface ResolvesAttachmentStorage
{
    public function disk(?string $visibilityScope = null): Filesystem;

    public function directory(?string $visibilityScope = null): string;
}
