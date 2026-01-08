<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class AccountUserRequest extends FormRequest
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
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'user_id' => $isUpdate ? 'nullable' : 'required|exists:users,id',
            'account_id' => $isUpdate ? 'nullable' : 'required|exists:accounts,id',
            'role' => 'required|in:agent,administrator',
            'availability' => 'nullable|integer|in:0,1',
            'active_at' => 'nullable|boolean',
            'settings' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'User is required.',
            'user_id.exists' => 'Selected user does not exist.',
            'account_id.required' => 'Account is required.',
            'account_id.exists' => 'Selected account does not exist.',
            'role.required' => 'Role is required.',
            'role.in' => 'Role must be either agent or admin.',
            'availability.in' => 'Availability must be either online (1) or offline (0).',
        ];
    }
}