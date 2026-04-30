<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AssignTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_type' => ['required', 'in:user,team,queue'],
            'target_id' => ['required'],
            'reason' => ['nullable', 'string'],
        ];
    }
}
