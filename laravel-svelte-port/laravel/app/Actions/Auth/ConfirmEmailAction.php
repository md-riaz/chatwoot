<?php

namespace App\Actions\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class ConfirmEmailAction
{
    use AsAction;

    /**
     * Confirm user's email address using token.
     */
    public function handle(string $token): User
    {
        $user = User::where('confirmation_token', $token)
            ->whereNull('email_verified_at')
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'token' => ['Invalid or expired confirmation token.']
            ]);
        }

        // Check if token is expired (24 hours)
        if ($user->created_at->diffInHours(now()) > 24) {
            throw ValidationException::withMessages([
                'token' => ['Confirmation token has expired. Please request a new one.']
            ]);
        }

        // Confirm email
        $user->email_verified_at = Carbon::now();
        $user->confirmation_token = null;
        $user->save();

        return $user;
    }

    /**
     * Get validation rules for the action.
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'size:64'],
        ];
    }
}