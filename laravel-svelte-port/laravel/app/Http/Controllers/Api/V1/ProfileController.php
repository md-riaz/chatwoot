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
     * 
     * Uses Sanctum's token system via HasAutoApiToken trait.
     */
    public function resetAccessToken(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Use Sanctum-based resetAccessToken from HasAutoApiToken trait
        $newToken = $user->resetAccessToken();
        
        // Reload user to get fresh data
        $user->refresh();
        $user->load('accountUsers.account');

        // Return user data with new token
        return response()->json($this->formatUserResponse($user, $newToken));
    }

    /**
     * Format user response to match Rails API format.
     */
    private function formatUserResponse(User $user, string $accessToken): array
    {
        $activeAccountUser = $user->accountUsers->first();

        return [
            'access_token' => $accessToken,
            'account_id' => $activeAccountUser?->account_id,
            'available_name' => $user->display_name ?? $user->name,
            'avatar_url' => $user->avatar_url,
            'confirmed' => !is_null($user->email_verified_at),
            'display_name' => $user->display_name,
            'message_signature' => $user->message_signature ?? '',
            'email' => $user->email,
            'id' => $user->id,
            'inviter_id' => $activeAccountUser?->inviter_id,
            'name' => $user->name,
            'provider' => $user->provider,
            'pubsub_token' => $user->pubsub_token,
            'custom_attributes' => $user->custom_attributes ?? [],
            'role' => $activeAccountUser?->role?->value ?? null,
            'ui_settings' => $user->ui_settings ?? [],
            'uid' => $user->uid,
            'type' => $user->type,
            'accounts' => $user->accountUsers->map(function ($accountUser) {
                return [
                    'id' => $accountUser->account_id,
                    'name' => $accountUser->account?->name ?? '',
                    'status' => $accountUser->account?->status ?? null,
                    'active_at' => $accountUser->active_at,
                    'role' => $accountUser->role?->value ?? null,
                    'availability' => $accountUser->availability?->value ?? null,
                    'auto_offline' => $accountUser->auto_offline ?? true,
                ];
            })->toArray(),
        ];
    }
}
