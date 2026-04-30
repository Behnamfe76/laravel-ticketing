<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\MultiTenancy;

interface ResolvesTenantContext
{
    public function id(): int|string|null;

    /**
     * @return array<string, mixed>
     */
    public function payload(): array;
}
