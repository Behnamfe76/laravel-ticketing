<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Services\SLA;

use Carbon\CarbonImmutable;
use Fereydooni\LaravelTicketing\Contracts\Automation\ComputesSLADeadlines;
use Fereydooni\LaravelTicketing\Models\SLAPolicy;

class SLADeadlineCalculator implements ComputesSLADeadlines
{
    public function compute(SLAPolicy $policy, ?CarbonImmutable $from = null): array
    {
        $from ??= CarbonImmutable::now();

        return [
            'first_response_due_at' => $policy->response_target_minutes > 0
                ? $from->addMinutes($policy->response_target_minutes)
                : null,
            'resolution_due_at' => $policy->resolution_target_minutes > 0
                ? $from->addMinutes($policy->resolution_target_minutes)
                : null,
        ];
    }
}
