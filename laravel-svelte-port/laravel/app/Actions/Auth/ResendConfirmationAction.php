<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class ResendConfirmationAction
{
    use AsAction;

    /**
     * Resend email confirmation to user.
     */
    public function handle(string $email): bool
    {
        $user = User::where('email', $email)
            ->whereNull('email_verified_at')
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Email not found or already confirmed.']
            ]);
        }

        // Check rate limiting (max 3 requests per hour)
        $recentAttempts = $user->updated_at->diffInMinutes(now()) < 20;
        if ($recentAttempts) {
            throw ValidationException::withMessages([
                'email' => ['Please wait before requesting another confirmation email.']
            ]);
        }

        // Send confirmation email
        SendEmailConfirmationAction::run($user);

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