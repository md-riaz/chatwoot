<?php

namespace App\Actions\Account;

use App\Exceptions\InvalidEmailException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class SignUpEmailValidationAction
{
    public function handle(string $email): bool
    {
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::info('Invalid email format', ['email' => $email]);
            throw new InvalidEmailException(['valid' => false, 'disposable' => null]);
        }

        // Check blocked domains
        if ($this->isDomainBlocked($email)) {
            Log::info('Blocked email domain', ['email' => $email]);
            throw new InvalidEmailException(['domain_blocked' => true]);
        }

        // Check disposable
        if ($this->isDisposable($email)) {
            Log::info('Disposable email detected', ['email' => $email]);
            throw new InvalidEmailException(['valid' => true, 'disposable' => true]);
        }

        return true;
    }

    private function isDomainBlocked(string $email): bool
    {
        $domain = strtolower(substr(strrchr($email, '@'), 1));
        $blockedDomains = $this->blockedDomains();
        
        foreach ($blockedDomains as $blocked) {
            if (stripos($domain, trim($blocked)) !== false) {
                return true;
            }
        }
        
        return false;
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
        // For now, just check some common disposable domains
        $disposableDomains = [
            '10minutemail.com',
            'tempmail.org',
            'guerrillamail.com',
            'mailinator.com',
        ];
        
        $domain = strtolower(substr(strrchr($email, '@'), 1));
        return in_array($domain, $disposableDomains);
    }
}
