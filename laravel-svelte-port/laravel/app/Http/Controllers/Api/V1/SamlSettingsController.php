<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\SamlAuthenticationHelper;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountSamlSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SamlSettingsController extends Controller
{
    /**
     * Display the SAML settings for an account.
     */
    public function show(Account $account): JsonResponse
    {
        $setting = AccountSamlSetting::where('account_id', $account->id)->first();

        if (! $setting) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => [
                'sso_url' => $setting->sso_url,
                'sp_entity_id' => $setting->sp_entity_id,
                'idp_entity_id' => $setting->idp_entity_id,
                'role_mappings' => $setting->role_mappings,
                'has_certificate' => ! empty($setting->certificate),
                'certificate_fingerprint' => $setting->certificate_fingerprint,
                'saml_enabled' => $setting->samlEnabled(),
                'metadata_url' => route('saml.metadata', ['account' => $account->id]),
                'login_url' => route('saml.login', ['account' => $account->id]),
            ],
        ]);
    }

    /**
     * Store SAML settings for an account.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'sso_url' => 'required|url',
            'certificate' => 'required|string',
            'sp_entity_id' => 'nullable|string',
            'idp_entity_id' => 'required|string',
            'role_mappings' => 'nullable|array',
            'enabled' => 'boolean',
            'issuer' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        // Validate certificate format
        $this->validateCertificate($validated['certificate']);

        // Validate identity provider configuration
        $this->validateIdpConfiguration($validated);

        $setting = AccountSamlSetting::updateOrCreate(
            ['account_id' => $account->id],
            $validated
        );

        return response()->json([
            'data' => [
                'sso_url' => $setting->sso_url,
                'sp_entity_id' => $setting->sp_entity_id,
                'idp_entity_id' => $setting->idp_entity_id,
                'role_mappings' => $setting->role_mappings,
                'has_certificate' => ! empty($setting->certificate),
                'certificate_fingerprint' => $setting->certificate_fingerprint,
                'saml_enabled' => $setting->samlEnabled(),
                'metadata_url' => route('saml.metadata', ['account' => $account->id]),
                'login_url' => route('saml.login', ['account' => $account->id]),
            ],
        ], 201);
    }

    /**
     * Update SAML settings for an account.
     */
    public function update(Request $request, Account $account): JsonResponse
    {
        $setting = AccountSamlSetting::where('account_id', $account->id)->firstOrFail();

        $validated = $request->validate([
            'sso_url' => 'url',
            'certificate' => 'nullable|string',
            'sp_entity_id' => 'nullable|string',
            'idp_entity_id' => 'nullable|string',
            'role_mappings' => 'nullable|array',
            'enabled' => 'boolean',
            'issuer' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        // Validate certificate format if provided
        if (isset($validated['certificate'])) {
            $this->validateCertificate($validated['certificate']);
        }

        // Validate identity provider configuration if provided
        if (isset($validated['sso_url']) || isset($validated['idp_entity_id'])) {
            $this->validateIdpConfiguration(array_merge($setting->toArray(), $validated));
        }

        $setting->update($validated);

        return response()->json([
            'data' => [
                'sso_url' => $setting->sso_url,
                'sp_entity_id' => $setting->sp_entity_id,
                'idp_entity_id' => $setting->idp_entity_id,
                'role_mappings' => $setting->role_mappings,
                'has_certificate' => ! empty($setting->certificate),
                'certificate_fingerprint' => $setting->certificate_fingerprint,
                'saml_enabled' => $setting->samlEnabled(),
                'metadata_url' => route('saml.metadata', ['account' => $account->id]),
                'login_url' => route('saml.login', ['account' => $account->id]),
            ],
        ]);
    }

    /**
     * Remove SAML settings for an account.
     */
    public function destroy(Account $account): JsonResponse
    {
        AccountSamlSetting::where('account_id', $account->id)->delete();

        return response()->json(null, 204);
    }

    /**
     * Validate X.509 certificate format
     */
    private function validateCertificate(string $certificate): void
    {
        try {
            $cert = openssl_x509_read($certificate);
            if (!$cert) {
                throw ValidationException::withMessages([
                    'certificate' => ['The certificate must be a valid X.509 certificate.']
                ]);
            }
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'certificate' => ['The certificate must be a valid X.509 certificate.']
            ]);
        }
    }

    /**
     * Validate identity provider configuration
     */
    private function validateIdpConfiguration(array $data): void
    {
        // Validate SSO URL is accessible
        if (isset($data['sso_url'])) {
            $ssoUrl = $data['sso_url'];
            
            // Basic URL validation
            if (!filter_var($ssoUrl, FILTER_VALIDATE_URL)) {
                throw ValidationException::withMessages([
                    'sso_url' => ['The SSO URL must be a valid URL.']
                ]);
            }

            // Check if URL uses HTTPS (recommended for production)
            if (app()->environment('production') && !str_starts_with($ssoUrl, 'https://')) {
                throw ValidationException::withMessages([
                    'sso_url' => ['The SSO URL should use HTTPS in production.']
                ]);
            }
        }

        // Validate Entity ID format
        if (isset($data['idp_entity_id'])) {
            $entityId = $data['idp_entity_id'];
            
            if (empty($entityId)) {
                throw ValidationException::withMessages([
                    'idp_entity_id' => ['The Identity Provider Entity ID is required.']
                ]);
            }

            // Entity ID should be a URI or URL
            if (!filter_var($entityId, FILTER_VALIDATE_URL) && !preg_match('/^urn:/', $entityId)) {
                throw ValidationException::withMessages([
                    'idp_entity_id' => ['The Identity Provider Entity ID must be a valid URI or URL.']
                ]);
            }
        }

        // Validate role mappings structure
        if (isset($data['role_mappings']) && is_array($data['role_mappings'])) {
            foreach ($data['role_mappings'] as $group => $mapping) {
                if (!is_array($mapping)) {
                    throw ValidationException::withMessages([
                        'role_mappings' => ['Each role mapping must be an array.']
                    ]);
                }

                // Validate role mapping has either 'role' or 'custom_role_id'
                if (!isset($mapping['role']) && !isset($mapping['custom_role_id'])) {
                    throw ValidationException::withMessages([
                        'role_mappings' => ["Role mapping for group '{$group}' must specify either 'role' or 'custom_role_id'."]
                    ]);
                }

                // Validate role values
                if (isset($mapping['role'])) {
                    $validRoles = ['agent', 'administrator'];
                    if (!in_array($mapping['role'], $validRoles)) {
                        throw ValidationException::withMessages([
                            'role_mappings' => ["Invalid role '{$mapping['role']}' for group '{$group}'. Valid roles are: " . implode(', ', $validRoles)]
                        ]);
                    }
                }
            }
        }
    }
}
