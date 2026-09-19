<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Taxonomy ids are checked for existence in the current tenant by the update action.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subject' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status_id' => ['sometimes', 'integer'],
            'priority_id' => ['sometimes', 'nullable', 'integer'],
            'category_id' => ['sometimes', 'nullable', 'integer'],
            'type_id' => ['sometimes', 'nullable', 'integer'],
        ];
    }
}
