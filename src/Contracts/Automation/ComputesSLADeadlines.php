<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\Automation;

use Carbon\CarbonImmutable;
use Fereydooni\LaravelTicketing\Models\SLAPolicy;

interface ComputesSLADeadlines
{
    /**
     * @return array{first_response_due_at:CarbonImmutable|null,resolution_due_at:CarbonImmutable|null}
     */
    public function compute(SLAPolicy $policy, ?CarbonImmutable $from = null): array;
}
