<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Services\Tenancy;

use Fereydooni\LaravelTicketing\Contracts\MultiTenancy\ResolvesTenantContext;

class TenantContext implements ResolvesTenantContext
{
    private int|string|null $tenantId = null;

    /**
     * @var array<string, mixed>
     */
    private array $payload = [];

    /**
     * @param array<string, mixed> $payload
     */
    public function set(int|string|null $tenantId, array $payload = []): void
    {
        $this->tenantId = $tenantId;
        $this->payload = $payload;
    }

    public function clear(): void
    {
        $this->tenantId = null;
        $this->payload = [];
    }

    public function id(): int|string|null
    {
        return $this->tenantId;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }

    /**
     * @return array{tenant_id:int|string|null,payload:array<string,mixed>}
     */
    public function snapshot(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'payload' => $this->payload,
        ];
    }

    /**
     * @param array{tenant_id?:int|string|null,payload?:array<string,mixed>} $snapshot
     */
    public function restore(array $snapshot): void
    {
        $this->tenantId = $snapshot['tenant_id'] ?? null;
        $this->payload = $snapshot['payload'] ?? [];
    }
}
