<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add policy check: $this->user()->can('update', $this->route('account'));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'locale' => ['sometimes', 'required', 'string', 'max:10'],
            'domain' => ['nullable', 'string'],
            'support_email' => ['nullable', 'email'],
            'settings' => ['nullable', 'array'],
            'features' => ['nullable', 'array'],
            'limits' => ['nullable', 'array'],
            'status' => ['sometimes', 'string', 'in:active,suspended'],
        ];
    }
}
