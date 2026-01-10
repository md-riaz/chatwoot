<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->type === 'SuperAdmin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $accountId = $this->route('account')?->id;

        return [
            'name' => 'required|string|max:255',
            'locale' => 'nullable|string|max:10',
            'domain' => [
                'nullable',
                'string',
                'max:255',
                $accountId ? "unique:accounts,domain,{$accountId}" : 'unique:accounts,domain'
            ],
            'support_email' => 'nullable|email|max:255',
            'settings' => 'nullable|array',
            'features' => 'nullable|array',
            'limits' => 'nullable|array',
            'status' => 'nullable|string|in:active,suspended',
            'enabled_features' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Account name is required.',
            'domain.unique' => 'This domain is already taken by another account.',
            'support_email.email' => 'Please provide a valid email address.',
            'status.in' => 'Status must be either active or suspended.',
        ];
    }
}