<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's profile.
     */
    public function show(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'display_name' => $user->display_name,
                'message_signature' => $user->message_signature,
                'ui_settings' => $user->ui_settings,
                'created_at' => $user->created_at,
            ]
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(Request $request): JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'string|max:255',
            'display_name' => 'nullable|string|max:255',
            'email' => 'email|unique:users,email,' . $user->id,
            'avatar_url' => 'nullable|url',
            'message_signature' => 'nullable|string',
            'ui_settings' => 'nullable|array',
        ]);

        $user->update($validated);

        return response()->json(['data' => $user]);
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'The current password is incorrect.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'Password updated successfully']);
    }

    /**
     * Update user availability.
     */
    public function updateAvailability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'availability' => 'required|string|in:online,offline,busy',
            'account_id' => 'required|exists:accounts,id',
        ]);

        $user = auth()->user();

        // Update availability in the pivot table
        $user->accounts()->updateExistingPivot($validated['account_id'], [
            'availability' => $validated['availability'],
        ]);

        return response()->json(['message' => 'Availability updated successfully']);
    }

    /**
     * Update user auto-offline setting.
     */
    public function updateAutoOffline(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'auto_offline' => 'required|boolean',
            'account_id' => 'required|exists:accounts,id',
        ]);

        $user = auth()->user();

        $user->accounts()->updateExistingPivot($validated['account_id'], [
            'auto_offline' => $validated['auto_offline'],
        ]);

        return response()->json(['message' => 'Auto-offline setting updated successfully']);
    }

    /**
     * Delete user avatar.
     */
    public function avatar(): JsonResponse
    {
        $user = auth()->user();

        $user->update(['avatar_url' => null]);

        return response()->json(null, 204);
    }

    /**
     * Set active account for user.
     */
    public function setActiveAccount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
        ]);

        $user = auth()->user();

        // Update active_at timestamp in pivot table
        $user->accounts()->updateExistingPivot($validated['account_id'], [
            'active_at' => now(),
        ]);

        return response()->json(null, 200);
    }

    /**
     * Resend confirmation email.
     */
    public function resendConfirmation(): JsonResponse
    {
        $user = auth()->user();

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email already confirmed'], 422);
        }

        // Queue confirmation email
        // $user->sendEmailVerificationNotification();

        return response()->json(null, 200);
    }

    /**
     * Reset access token.
     */
    public function resetAccessToken(): JsonResponse
    {
        $user = auth()->user();

        // Revoke all existing tokens
        $user->tokens()->delete();

        // Create a new token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
        ]);
    }
}
