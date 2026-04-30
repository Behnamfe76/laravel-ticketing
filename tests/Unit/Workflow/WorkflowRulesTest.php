<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Unit\Workflow;

use Carbon\CarbonImmutable;
use Fereydooni\LaravelTicketing\Actions\Tickets\CreateTicketAction;
use Fereydooni\LaravelTicketing\Contracts\Search\SearchesTickets;
use Fereydooni\LaravelTicketing\Models\AutomationRule;
use Fereydooni\LaravelTicketing\Models\SLAPolicy;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Models\Tag;
use Fereydooni\LaravelTicketing\Services\SLA\EscalationEngine;
use Fereydooni\LaravelTicketing\Services\SLA\SLADeadlineCalculator;
use Fereydooni\LaravelTicketing\Tests\Concerns\SetsUpTicketingDatabase;
use Fereydooni\LaravelTicketing\Tests\TestCase;

class WorkflowRulesTest extends TestCase
{
    use SetsUpTicketingDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateTicketingDatabase();
    }

    public function test_search_filters_by_term_and_tag(): void
    {
        $status = Status::query()->create(['name' => 'Open', 'slug' => 'open', 'is_default' => true]);
        $tag = Tag::query()->create(['name' => 'VIP', 'slug' => 'vip']);
        $ticket = app(CreateTicketAction::class)->create([
            'subject' => 'VIP account issue',
            'status_id' => $status->getKey(),
        ], $this->user());
        $ticket->tags()->attach($tag);
        app(CreateTicketAction::class)->create(['subject' => 'Regular question'], $this->user());

        $results = app(SearchesTickets::class)->search(['search' => 'VIP', 'tag_id' => $tag->getKey()]);

        $this->assertCount(1, $results);
        $this->assertSame($ticket->getKey(), $results->first()->getKey());
    }

    public function test_sla_deadlines_are_calculated_from_policy_targets(): void
    {
        $policy = SLAPolicy::query()->create([
            'name' => 'Gold',
            'slug' => 'gold',
            'response_target_minutes' => 30,
            'resolution_target_minutes' => 240,
        ]);
        $from = CarbonImmutable::parse('2026-04-30 12:00:00');

        $deadlines = app(SLADeadlineCalculator::class)->compute($policy, $from);

        $this->assertTrue($deadlines['first_response_due_at']->equalTo($from->addMinutes(30)));
        $this->assertTrue($deadlines['resolution_due_at']->equalTo($from->addMinutes(240)));
    }

    public function test_automation_rules_apply_in_priority_order_and_stop_processing(): void
    {
        $status = Status::query()->create(['name' => 'Open', 'slug' => 'open', 'is_default' => true]);
        $ticket = app(CreateTicketAction::class)->create(['subject' => 'Escalate me', 'status_id' => $status->getKey()], $this->user());

        AutomationRule::query()->create(['name' => 'Second', 'slug' => 'second', 'trigger' => 'ticket_created', 'priority' => 20]);
        AutomationRule::query()->create(['name' => 'First stop', 'slug' => 'first-stop', 'trigger' => 'ticket_created', 'priority' => 10, 'stop_processing' => true]);

        $rules = app(EscalationEngine::class)->matching('ticket_created', $ticket);

        $this->assertCount(1, $rules);
        $this->assertSame('first-stop', $rules->first()->slug);
    }
}
