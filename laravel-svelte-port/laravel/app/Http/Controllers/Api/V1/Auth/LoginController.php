<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @group Authentication
 * 
 * API endpoints for user authentication and session management
 */
class LoginController extends Controller
{
    /**
     * Login user and create API token
     * 
     * Authenticate a user with email and password to obtain an API token
     * for subsequent authenticated requests.
     * 
     * @bodyParam email string required The user's email address. Example: user@example.com
     * @bodyParam password string required The user's password. Example: secret123
     * 
     * @response 200 scenario="Login successful"
     * @response 401 scenario="Invalid credentials"
     * @response 422 scenario="Validation error"
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with(['accountUsers.account', 'roles'])
            ->where('email', $request->email)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * Logout user and revoke current token
     * 
     * Revoke the current authentication token and end the user session.
     * 
     * @response 204 scenario="Logout successful"
     * @response 401 scenario="Unauthenticated"
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(null, 204);
    }

    /**
     * Get authenticated user
     * 
     * Retrieve the currently authenticated user's profile information.
     * 
     * @response 200 scenario="User retrieved"
     * @response 401 scenario="Unauthenticated"
     */
    public function me(Request $request): UserResource
    {
        $user = $request->user()->load(['accountUsers.account', 'roles']);
        return new UserResource($user);
    }
}
