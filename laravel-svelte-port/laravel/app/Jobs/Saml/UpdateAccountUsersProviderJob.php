<?php

namespace App\Jobs\Saml;

use App\Models\Account;
use App\Models\AccountSamlSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateAccountUsersProviderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $accountId,
        private string $provider
    ) {}

    /**
     * Updates the authentication provider for users in an account
     * This job is triggered when SAML settings are created or destroyed
     */
    public function handle(): void
    {
        $account = Account::find($this->accountId);
        if (!$account) {
            return;
        }

        $account->users()->chunk(1000, function ($users) {
            foreach ($users as $user) {
                if ($this->shouldUpdateUserProvider($user)) {
                    // Use update to bypass model events for performance
                    $user->update(['provider' => $this->provider]);
                }
            }
        });
    }

    /**
     * Determines if a user's provider should be updated based on their multi-account status
     * When resetting to 'email', only update users who don't have SAML enabled on other accounts
     * This prevents breaking SAML authentication for users who belong to multiple accounts
     */
    private function shouldUpdateUserProvider($user): bool
    {
        if ($this->provider === 'email') {
            return !$this->userHasOtherSamlAccounts($user);
        }

        return true;
    }

    /**
     * Checks if the user belongs to any other accounts that have SAML configured
     * Used to preserve SAML authentication when one account disables SAML but others still use it
     */
    private function userHasOtherSamlAccounts($user): bool
    {
        return $user->accounts()
            ->whereHas('samlSettings')
            ->where('accounts.id', '!=', $this->accountId)
            ->exists();
    }
}