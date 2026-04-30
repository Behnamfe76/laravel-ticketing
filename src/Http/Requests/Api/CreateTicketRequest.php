<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority_id' => ['nullable', 'integer'],
            'category_id' => ['nullable', 'integer'],
            'type_id' => ['nullable', 'integer'],
            'custom_fields' => ['nullable', 'array'],
            'tags' => ['nullable', 'array'],
            'watchers' => ['nullable', 'array'],
        ];
    }
}
