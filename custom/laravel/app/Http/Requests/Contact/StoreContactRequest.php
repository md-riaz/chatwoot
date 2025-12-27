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
        // Get account_id from route
        $accountId = $this->route('account')?->id;

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                $accountId ? "unique:contacts,email,NULL,id,account_id,{$accountId}" : 'unique:contacts,email',
            ],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'identifier' => ['nullable', 'string'],
            'avatar_url' => ['nullable', 'url'],
            'custom_attributes' => ['nullable', 'array'],
            'additional_attributes' => ['nullable', 'array'],
        ];
    }
}
