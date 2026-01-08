<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add policy check
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string'],
            'message_type' => ['nullable', 'integer', 'in:0,1,2,3'],
            'content_type' => ['nullable', 'integer', 'in:0,1,2,3,4,5,6'],
            'private' => ['nullable', 'boolean'],
            'content_attributes' => ['nullable', 'array'],
        ];
    }
}
