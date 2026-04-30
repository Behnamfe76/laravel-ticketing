<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Services\SLA;

use Fereydooni\LaravelTicketing\Contracts\Automation\EvaluatesAutomationRules;
use Fereydooni\LaravelTicketing\Models\AutomationRule;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Support\Collection;

class EscalationEngine implements EvaluatesAutomationRules
{
    public function matching(string $trigger, Ticket $ticket): Collection
    {
        $rules = AutomationRule::query()
            ->where('trigger', $trigger)
            ->where('is_active', true)
            ->orderBy('priority')
            ->get()
            ->filter(fn (AutomationRule $rule) => $this->conditionsMatch($rule, $ticket))
            ->values();

        $selected = collect();

        foreach ($rules as $rule) {
            $selected->push($rule);

            if ($rule->stop_processing) {
                break;
            }
        }

        return $selected;
    }

    protected function conditionsMatch(AutomationRule $rule, Ticket $ticket): bool
    {
        foreach (($rule->conditions ?? []) as $field => $expected) {
            if ((string) data_get($ticket, $field) !== (string) $expected) {
                return false;
            }
        }

        return true;
    }
}
