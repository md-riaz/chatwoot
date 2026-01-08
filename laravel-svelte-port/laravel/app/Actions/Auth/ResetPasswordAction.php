<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class ResetPasswordAction
{
    use AsAction;

    /**
     * Reset user's password using token.
     */
    public function handle(string $email, string $token, string $password): User
    {
        // Find reset token
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$resetRecord || !Hash::check($token, $resetRecord->token)) {
            throw ValidationException::withMessages([
                'token' => ['Invalid or expired password reset token.']
            ]);
        }

        // Check if token is expired (1 hour)
        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            throw ValidationException::withMessages([
                'token' => ['Password reset token has expired. Please request a new one.']
            ]);
        }

        // Find user
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['User not found.']
            ]);
        }

        // Update password
        $user->password = Hash::make($password);
        $user->email_verified_at = $user->email_verified_at ?? now(); // Auto-confirm email on password reset
        $user->save();

        // Delete reset token
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Revoke all existing tokens for security
        $user->tokens()->delete();

        return $user;
    }

    /**
     * Get validation rules for the action.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'token' => ['required', 'string', 'size:64'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}