<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\Automation;

use Fereydooni\LaravelTicketing\Models\AutomationRule;
use Fereydooni\LaravelTicketing\Models\Ticket;

interface ExecutesAutomationActions
{
    public function execute(AutomationRule $rule, Ticket $ticket): void;
}
