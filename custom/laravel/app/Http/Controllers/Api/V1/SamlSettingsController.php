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
}
