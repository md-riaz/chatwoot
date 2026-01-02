<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\ResetPasswordAction;
use App\Actions\Auth\SendPasswordResetAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    /**
     * Send password reset link to user's email.
     */
    public function sendResetLink(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            SendPasswordResetAction::run($request->email);

            return response()->json([
                'message' => 'Password reset link sent to your email.',
                'data' => [
                    'email_sent' => true,
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Failed to send password reset link.',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Reset user's password.
     */
    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string', 'size:64'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        try {
            $user = ResetPasswordAction::run(
                $request->email,
                $request->token,
                $request->password
            );

            return response()->json([
                'message' => 'Password reset successfully.',
                'data' => [
                    'user' => $user,
                    'password_reset' => true,
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Password reset failed.',
                'errors' => $e->errors()
            ], 422);
        }
    }
}