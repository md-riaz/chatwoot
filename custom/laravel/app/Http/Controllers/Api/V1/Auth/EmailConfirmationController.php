<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\ConfirmEmailAction;
use App\Actions\Auth\ResendConfirmationAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EmailConfirmationController extends Controller
{
    /**
     * Confirm user's email address.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string', 'size:64'],
        ]);

        try {
            $user = ConfirmEmailAction::run($request->token);

            return response()->json([
                'message' => 'Email confirmed successfully.',
                'data' => [
                    'user' => $user,
                    'email_verified' => true,
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Email confirmation failed.',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Resend email confirmation.
     */
    public function resend(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            ResendConfirmationAction::run($request->email);

            return response()->json([
                'message' => 'Confirmation email sent successfully.',
                'data' => [
                    'email_sent' => true,
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Failed to send confirmation email.',
                'errors' => $e->errors()
            ], 422);
        }
    }
}