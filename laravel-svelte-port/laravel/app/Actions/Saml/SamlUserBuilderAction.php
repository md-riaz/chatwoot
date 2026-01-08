<?php

namespace App\Actions\Saml;

use App\Models\Account;
use App\Models\AccountSamlSetting;
use App\Models\AccountUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class SamlUserBuilderAction
{
    use AsAction;

    private array $authHash;
    private int $accountId;
    private ?AccountSamlSetting $samlSettings;

    public function handle(array $authHash, int $accountId): ?User
    {
        $this->authHash = $authHash;
        $this->accountId = $accountId;
        $this->samlSettings = AccountSamlSetting::where('account_id', $accountId)->first();

        $user = $this->findOrCreateUser();
        
        if ($user && $user->exists) {
            $this->addUserToAccount($user);
        }

        return $user;
    }

    private function findOrCreateUser(): ?User
    {
        $email = $this->getAuthAttribute('email');
        if (!$email) {
            return null;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $this->confirmUserIfRequired($user);
            $this->convertExistingUserToSaml($user);
            return $user;
        }

        return $this->createUser();
    }

    private function confirmUserIfRequired(User $user): void
    {
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            $user->save();
        }
    }

    private function convertExistingUserToSaml(User $user): void
    {
        if ($user->provider !== 'saml') {
            $user->update(['provider' => 'saml']);
        }
    }

    private function createUser(): User
    {
        $firstName = $this->getAuthAttribute('first_name');
        $lastName = $this->getAuthAttribute('last_name');
        $fullName = trim(($firstName ?? '') . ' ' . ($lastName ?? ''));
        
        $fallbackName = $this->getAuthAttribute('name') ?? 
                       explode('@', $this->getAuthAttribute('email'))[0];

        return User::create([
            'email' => $this->getAuthAttribute('email'),
            'name' => $fullName ?: $fallbackName,
            'display_name' => $firstName,
            'provider' => 'saml',
            'uid' => $this->getUid(),
            'password' => Hash::make(Str::random(32)),
            'email_verified_at' => now(),
        ]);
    }

    private function addUserToAccount(User $user): void
    {
        $account = Account::find($this->accountId);
        if (!$account) {
            return;
        }

        // Create account_user if not exists
        $accountUser = AccountUser::firstOrCreate([
            'user_id' => $user->id,
            'account_id' => $account->id,
        ], [
            'role' => 'agent', // Default role
        ]);

        // Handle role mappings if configured
        $this->applyRoleMappings($accountUser, $account);
    }

    private function applyRoleMappings(AccountUser $accountUser, Account $account): void
    {
        $matchingMapping = $this->findMatchingRoleMapping($account);
        if (!$matchingMapping) {
            return;
        }

        if (isset($matchingMapping['role'])) {
            $accountUser->update(['role' => $matchingMapping['role']]);
        } elseif (isset($matchingMapping['custom_role_id'])) {
            $accountUser->update(['custom_role_id' => $matchingMapping['custom_role_id']]);
        }
    }

    private function findMatchingRoleMapping(Account $account): ?array
    {
        if (!$this->samlSettings || empty($this->samlSettings->role_mappings)) {
            return null;
        }

        $samlGroups = $this->getSamlGroups();
        
        foreach ($samlGroups as $group) {
            if (isset($this->samlSettings->role_mappings[$group])) {
                return $this->samlSettings->role_mappings[$group];
            }
        }

        return null;
    }

    private function getAuthAttribute(string $key, $fallback = null)
    {
        return $this->authHash['info'][$key] ?? $fallback;
    }

    private function getUid(): string
    {
        return $this->authHash['uid'] ?? '';
    }

    private function getSamlGroups(): array
    {
        // Groups can come from different attributes depending on IdP
        $rawInfo = $this->authHash['extra']['raw_info'] ?? [];
        
        return $rawInfo['groups'] ?? 
               $rawInfo['Group'] ?? 
               $rawInfo['memberOf'] ?? 
               [];
    }
}