<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Unit\Forms;

use Fereydooni\LaravelTicketing\Models\CustomFieldDefinition;
use Fereydooni\LaravelTicketing\Support\Forms\CustomFieldFormBuilder;
use Fereydooni\LaravelTicketing\Support\Forms\CustomFieldValidator;
use Fereydooni\LaravelTicketing\Tests\Concerns\SetsUpTicketingDatabase;
use Fereydooni\LaravelTicketing\Tests\TestCase;

class CustomFieldValidationTest extends TestCase
{
    use SetsUpTicketingDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateTicketingDatabase();
    }

    public function test_validator_enforces_required_type_and_visibility_rules(): void
    {
        $field = CustomFieldDefinition::query()->create([
            'scope' => 'ticket',
            'name' => 'Impact',
            'slug' => 'impact',
            'field_type' => 'number',
            'label' => 'Impact',
            'is_required' => true,
            'visibility_rules' => ['category' => 'incident'],
        ]);

        $validator = app(CustomFieldValidator::class);

        $this->assertFalse($validator->validate([$field], ['category' => 'request'])->has('impact'));
        $this->assertTrue($validator->validate([$field], ['category' => 'incident'])->has('impact'));
        $this->assertTrue($validator->validate([$field], ['category' => 'incident', 'impact' => 'high'])->has('impact'));
        $this->assertSame('42', $validator->normalize($field, '42'));
    }

    public function test_form_builder_returns_active_fields_in_display_order(): void
    {
        CustomFieldDefinition::query()->create(['scope' => 'ticket', 'name' => 'Second', 'slug' => 'second', 'field_type' => 'text', 'label' => 'Second', 'display_order' => 20]);
        CustomFieldDefinition::query()->create(['scope' => 'ticket', 'name' => 'First', 'slug' => 'first', 'field_type' => 'text', 'label' => 'First', 'display_order' => 10]);

        $schema = app(CustomFieldFormBuilder::class)->schema('ticket');

        $this->assertSame(['first', 'second'], $schema->pluck('slug')->all());
    }
}
