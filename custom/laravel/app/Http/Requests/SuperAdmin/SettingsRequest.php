<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
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
        return [
            'settings' => 'required|array',
            'settings.*' => 'required',
            'force' => 'nullable|boolean',
            'name' => 'sometimes|required|string|unique:installation_configs,name',
            'value' => 'sometimes|required',
            'locked' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'settings.required' => 'Settings data is required.',
            'settings.array' => 'Settings must be provided as an array.',
            'settings.*.required' => 'Each setting must have a value.',
            'name.required' => 'Setting name is required.',
            'name.unique' => 'A setting with this name already exists.',
            'value.required' => 'Setting value is required.',
        ];
    }
}