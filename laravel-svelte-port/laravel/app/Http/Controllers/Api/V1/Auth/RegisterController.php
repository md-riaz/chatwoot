<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\SendEmailConfirmationAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'confirmation_token' => Str::random(64),
        ]);

        event(new Registered($user));

        // Send email confirmation
        SendEmailConfirmationAction::run($user);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful. Please check your email to confirm your account.',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'email_confirmation_sent' => true,
            ]
        ], 201);
    }
}
