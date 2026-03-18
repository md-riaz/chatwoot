<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

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
        $nameRule = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'name' => [$nameRule, 'string', 'max:255'],
            'locale' => 'nullable|string|max:10',
            'domain' => [
                'nullable',
                'string',
                'max:255',
                $accountId ? "unique:accounts,domain,{$accountId}" : 'unique:accounts,domain'
            ],
            'support_email' => 'nullable|email|max:255',
            'auto_resolve_duration' => 'nullable|integer',
            'settings' => 'nullable|array',
            'limits' => 'nullable|array',
            'custom_attributes' => 'nullable|array',
            'internal_attributes' => 'nullable|array',
            'status' => 'nullable|string|in:active,suspended',
            'enabled_features' => 'nullable|array',
            'selected_feature_flags' => 'nullable|array',
            'manually_managed_features' => 'nullable|array',
        ];
    }

    protected function prepareForValidation(): void
    {
        $selectedFeatureFlags = $this->input('selected_feature_flags', $this->input('selectedFeatureFlags'));
        $enabledFeatures = $this->input('enabled_features');
        $manuallyManagedFeatures = $this->input('manually_managed_features', $this->input('manuallyManagedFeatures'));
        $limits = $this->input('limits');

        if (is_array($selectedFeatureFlags)) {
            $selectedFeatureFlags = $this->normalizeFeatureSelection($selectedFeatureFlags);
        }

        if (is_array($enabledFeatures)) {
            $enabledFeatures = array_values(array_map(
                fn (string $feature) => Str::snake(str_replace('feature_', '', $feature)),
                array_keys(array_filter($enabledFeatures))
            ));
        }

        if (is_array($manuallyManagedFeatures)) {
            $manuallyManagedFeatures = array_values(array_map(
                fn (mixed $feature) => Str::snake((string) $feature),
                $manuallyManagedFeatures
            ));
        }

        if (is_array($limits)) {
            $limits = array_filter($limits, static fn (mixed $value) => $value !== null && $value !== '');
        }

        $this->merge(array_filter([
            'selected_feature_flags' => $selectedFeatureFlags,
            'enabled_features' => $enabledFeatures,
            'manually_managed_features' => $manuallyManagedFeatures,
            'limits' => $limits,
        ], static fn (mixed $value) => $value !== null));
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

    /**
     * @param array<int|string, mixed> $selectedFeatureFlags
     * @return array<int, string>
     */
    private function normalizeFeatureSelection(array $selectedFeatureFlags): array
    {
        if (array_is_list($selectedFeatureFlags)) {
            return array_values(array_map(
                fn (mixed $feature) => Str::snake((string) $feature),
                array_filter($selectedFeatureFlags, static fn (mixed $feature) => is_string($feature) && $feature !== '')
            ));
        }

        return array_values(array_map(
            fn (string $feature) => Str::snake($feature),
            array_keys(array_filter($selectedFeatureFlags))
        ));
    }
}
