<?php

namespace App\Actions\Auth;

use App\Mail\EmailConfirmation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class SendEmailConfirmationAction
{
    use AsAction;

    /**
     * Send email confirmation to user.
     */
    public function handle(User $user): bool
    {
        // Generate confirmation token if not exists
        if (empty($user->confirmation_token)) {
            $user->confirmation_token = Str::random(64);
            $user->save();
        }

        // Send confirmation email
        Mail::to($user->email)->send(new EmailConfirmation($user));

        return true;
    }

    /**
     * Get validation rules for the action.
     */
    public function rules(): array
    {
        return [];
    }
}