<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add policy check
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'identifier' => ['nullable', 'string'],
            'avatar_url' => ['nullable', 'url'],
            'custom_attributes' => ['nullable', 'array'],
            'additional_attributes' => ['nullable', 'array'],
        ];
    }
}
