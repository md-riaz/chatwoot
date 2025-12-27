<?php

namespace App\Http\Controllers\Api\V1\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class MfaController extends Controller
{
    /**
     * Show MFA status for the user.
     * GET /api/v1/profile/mfa
     */
    public function show(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'mfa_enabled' => $user->mfa_enabled ?? false,
        ]);
    }

    /**
     * Enable MFA for the user (generates secret).
     * POST /api/v1/profile/mfa
     */
    public function store(): JsonResponse
    {
        $user = auth()->user();

        if ($user->mfa_enabled) {
            return response()->json([
                'error' => 'MFA is already enabled',
            ], 422);
        }

        // Generate new secret
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        // Store secret temporarily (not yet verified)
        $user->update([
            'otp_secret_key' => $secret,
        ]);

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return response()->json([
            'provisioning_uri' => $qrCodeUrl,
        ], 201);
    }

    /**
     * Verify OTP and activate MFA.
     * POST /api/v1/profile/mfa/verify
     */
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'otp_code' => 'required|string|size:6',
        ]);

        $user = auth()->user();

        if ($user->mfa_enabled) {
            return response()->json([
                'error' => 'MFA is already enabled',
            ], 422);
        }

        if (empty($user->otp_secret_key)) {
            return response()->json([
                'error' => 'MFA not initialized. Please call POST /profile/mfa first.',
            ], 422);
        }

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->otp_secret_key, $validated['otp_code']);

        if (!$valid) {
            return response()->json([
                'error' => 'Invalid OTP code',
            ], 422);
        }

        // Generate backup codes
        $backupCodes = $this->generateBackupCodes();

        $user->update([
            'mfa_enabled' => true,
            'mfa_backup_codes' => $backupCodes,
        ]);

        return response()->json([
            'backup_codes' => $backupCodes,
        ]);
    }

    /**
     * Disable MFA.
     * DELETE /api/v1/profile/mfa
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'otp_code' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = auth()->user();

        if (!$user->mfa_enabled) {
            return response()->json([
                'error' => 'MFA is not enabled',
            ], 422);
        }

        // Verify password
        if (!Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'error' => 'Invalid credentials',
            ], 422);
        }

        // Verify OTP
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->otp_secret_key, $validated['otp_code']);

        // Also check backup codes
        if (!$valid) {
            $backupCodes = $user->mfa_backup_codes ?? [];
            $valid = in_array($validated['otp_code'], $backupCodes);
        }

        if (!$valid) {
            return response()->json([
                'error' => 'Invalid OTP code',
            ], 422);
        }

        $user->update([
            'mfa_enabled' => false,
            'otp_secret_key' => null,
            'mfa_backup_codes' => null,
        ]);

        return response()->json(null, 200);
    }

    /**
     * Generate new backup codes.
     * POST /api/v1/profile/mfa/backup_codes
     */
    public function backupCodes(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'otp_code' => 'required|string',
        ]);

        $user = auth()->user();

        if (!$user->mfa_enabled) {
            return response()->json([
                'error' => 'MFA is not enabled',
            ], 422);
        }

        // Verify OTP
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->otp_secret_key, $validated['otp_code']);

        if (!$valid) {
            return response()->json([
                'error' => 'Invalid OTP code',
            ], 422);
        }

        // Generate new backup codes
        $backupCodes = $this->generateBackupCodes();

        $user->update([
            'mfa_backup_codes' => $backupCodes,
        ]);

        return response()->json([
            'backup_codes' => $backupCodes,
        ]);
    }

    /**
     * Generate a set of backup codes.
     */
    private function generateBackupCodes(int $count = 10): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }
        return $codes;
    }
}
