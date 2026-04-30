<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\Automation;

use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Support\Collection;

interface EvaluatesAutomationRules
{
    /**
     * @return Collection<int, \Fereydooni\LaravelTicketing\Models\AutomationRule>
     */
    public function matching(string $trigger, Ticket $ticket): Collection;
}
