<?php

namespace App\Services\Email;

use App\Models\Account;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

class TemplateResolverService
{
    /**
     * Resolve email template with priority: Account > Installation > File.
     */
    public function resolve(string $templateName, ?Account $account = null): string
    {
        $cacheKey = "email_template_{$templateName}_" . ($account?->id ?? 'global');
        $cacheTtl = config('email.templates.cache_ttl', 3600);
        
        return Cache::remember($cacheKey, $cacheTtl, function () use ($templateName, $account) {
            // Priority 1: Account-specific template
            if ($account) {
                $accountTemplate = $this->getAccountTemplate($templateName, $account);
                if ($accountTemplate) {
                    return $accountTemplate;
                }
            }

            // Priority 2: Installation-specific template
            $installationTemplate = $this->getInstallationTemplate($templateName);
            if ($installationTemplate) {
                return $installationTemplate;
            }

            // Priority 3: File-based template
            return $this->getFileTemplate($templateName);
        });
    }

    /**
     * Get account-specific email template.
     */
    protected function getAccountTemplate(string $templateName, Account $account): ?string
    {
        $locale = app()->getLocale();
        $fallbackLocale = config('email.templates.fallback_locale', 'en');
        
        // Try current locale first
        $template = EmailTemplate::where('account_id', $account->id)
            ->where('name', $templateName)
            ->where('locale', $locale)
            ->first();

        // Fallback to default locale if not found
        if (!$template && $locale !== $fallbackLocale) {
            $template = EmailTemplate::where('account_id', $account->id)
                ->where('name', $templateName)
                ->where('locale', $fallbackLocale)
                ->first();
        }

        return $template?->body;
    }

    /**
     * Get installation-specific email template.
     */
    protected function getInstallationTemplate(string $templateName): ?string
    {
        $locale = app()->getLocale();
        $fallbackLocale = config('email.templates.fallback_locale', 'en');
        
        // Try current locale first
        $template = EmailTemplate::whereNull('account_id')
            ->where('name', $templateName)
            ->where('locale', $locale)
            ->first();

        // Fallback to default locale if not found
        if (!$template && $locale !== $fallbackLocale) {
            $template = EmailTemplate::whereNull('account_id')
                ->where('name', $templateName)
                ->where('locale', $fallbackLocale)
                ->first();
        }

        return $template?->body;
    }

    /**
     * Get file-based email template.
     */
    protected function getFileTemplate(string $templateName): string
    {
        $viewPath = "emails.{$templateName}";
        
        if (View::exists($viewPath)) {
            return $viewPath;
        }

        // Fallback to generic template
        return 'emails.generic-notification';
    }

    /**
     * Clear template cache.
     */
    public function clearCache(?Account $account = null, ?string $templateName = null): void
    {
        if ($templateName && $account) {
            Cache::forget("email_template_{$templateName}_{$account->id}");
        } elseif ($templateName) {
            Cache::forget("email_template_{$templateName}_global");
        } else {
            // Clear all email template cache
            $pattern = 'email_template_*';
            $keys = Cache::getRedis()->keys($pattern);
            if (!empty($keys)) {
                Cache::getRedis()->del($keys);
            }
        }
    }

    /**
     * Get all available templates for an account.
     */
    public function getAvailableTemplates(?Account $account = null): array
    {
        $templates = [];

        // Get database templates
        $query = EmailTemplate::select('name', 'locale', 'account_id');
        if ($account) {
            $query->where(function ($q) use ($account) {
                $q->where('account_id', $account->id)
                  ->orWhereNull('account_id');
            });
        } else {
            $query->whereNull('account_id');
        }

        $dbTemplates = $query->get();
        foreach ($dbTemplates as $template) {
            $key = $template->name . '_' . $template->locale;
            $templates[$key] = [
                'name' => $template->name,
                'locale' => $template->locale,
                'source' => $template->account_id ? 'account' : 'installation',
            ];
        }

        // Get file-based templates
        $fileTemplates = $this->getFileBasedTemplates();
        foreach ($fileTemplates as $template) {
            $key = $template . '_' . app()->getLocale();
            if (!isset($templates[$key])) {
                $templates[$key] = [
                    'name' => $template,
                    'locale' => app()->getLocale(),
                    'source' => 'file',
                ];
            }
        }

        return array_values($templates);
    }

    /**
     * Get file-based email templates.
     */
    protected function getFileBasedTemplates(): array
    {
        $templatePath = resource_path('views/emails');
        $templates = [];

        if (is_dir($templatePath)) {
            $files = glob($templatePath . '/**/*.blade.php');
            foreach ($files as $file) {
                $relativePath = str_replace($templatePath . '/', '', $file);
                $templateName = str_replace(['/', '.blade.php'], ['.', ''], $relativePath);
                $templates[] = $templateName;
            }
        }

        return $templates;
    }
}