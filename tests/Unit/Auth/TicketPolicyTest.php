<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Unit\Auth;

use Fereydooni\LaravelTicketing\Actions\Tickets\CreateTicketAction;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Policies\TicketPolicy;
use Fereydooni\LaravelTicketing\Tests\Concerns\SetsUpTicketingDatabase;
use Fereydooni\LaravelTicketing\Tests\TestCase;

class TicketPolicyTest extends TestCase
{
    use SetsUpTicketingDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateTicketingDatabase();

        Status::query()->create(['name' => 'Open', 'slug' => 'open', 'is_default' => true]);
    }

    public function test_requester_can_view_and_reply_but_not_assign_or_manage_without_staff_abilities(): void
    {
        $requester = $this->user();
        $ticket = app(CreateTicketAction::class)->create(['subject' => 'Need help'], $requester);
        $policy = app(TicketPolicy::class);

        $this->assertTrue($policy->view($requester, $ticket));
        $this->assertTrue($policy->reply($requester, $ticket));
        $this->assertFalse($policy->assign($requester, $ticket));
        $this->assertFalse($policy->manage($requester, $ticket));
    }

    public function test_staff_ability_mapping_allows_internal_actions(): void
    {
        $requester = $this->user();
        $staff = $this->user(['ticket.note', 'ticket.assign', 'ticket.manage']);
        $ticket = app(CreateTicketAction::class)->create(['subject' => 'Need help'], $requester);
        $policy = app(TicketPolicy::class);

        $this->assertTrue($policy->note($staff, $ticket));
        $this->assertTrue($policy->assign($staff, $ticket));
        $this->assertTrue($policy->manage($staff, $ticket));
    }
}
