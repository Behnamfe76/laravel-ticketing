<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AddReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entry_type' => ['nullable', 'in:public_reply,internal_note'],
            'body' => ['required', 'string'],
            'attachments' => ['nullable', 'array'],
            'mentions' => ['nullable', 'array'],
        ];
    }
}
