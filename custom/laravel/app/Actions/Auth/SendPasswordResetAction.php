<?php

namespace App\Actions\Auth;

use App\Mail\PasswordResetNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class SendPasswordResetAction
{
    use AsAction;

    /**
     * Send password reset email to user.
     */
    public function handle(string $email): bool
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['We can\'t find a user with that email address.']
            ]);
        }

        // Check rate limiting (max 3 requests per hour)
        $recentReset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('created_at', '>', now()->subHour())
            ->count();

        if ($recentReset >= 3) {
            throw ValidationException::withMessages([
                'email' => ['Too many password reset attempts. Please try again later.']
            ]);
        }

        // Generate reset token
        $token = Str::random(64);

        // Store reset token
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Send reset email
        Mail::to($user->email)->send(new PasswordResetNotification($user, $token));

        return true;
    }

    /**
     * Get validation rules for the action.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}