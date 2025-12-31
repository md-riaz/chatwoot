<?php

namespace App\Actions\Account;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class SignUpEmailValidationAction
{
    public function handle(string $email): bool
    {
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::info('Invalid email format', ['email' => $email]);
            // TODO: throw custom InvalidEmail exception with details
            return false;
        }

        // Check blocked domains
        $domain = strtolower(substr(strrchr($email, '@'), 1));
        $blockedDomains = $this->blockedDomains();
        foreach ($blockedDomains as $blocked) {
            if (stripos($domain, trim($blocked)) !== false) {
                Log::info('Blocked email domain', ['email' => $email, 'domain' => $domain]);
                // TODO: throw custom InvalidEmail exception with domain_blocked
                return false;
            }
        }

        // Check disposable (placeholder, implement with package if needed)
        if ($this->isDisposable($email)) {
            Log::info('Disposable email detected', ['email' => $email]);
            // TODO: throw custom InvalidEmail exception with disposable flag
            return false;
        }

        return true;
    }

    private function blockedDomains(): array
    {
        $domains = Config::get('blocked_email_domains', '');
        if (empty($domains)) {
            return [];
        }
        return array_map('trim', explode("\n", $domains));
    }

    private function isDisposable(string $email): bool
    {
        // TODO: Integrate with a disposable email checker package
        return false;
    }
}
