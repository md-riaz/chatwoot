<?php

namespace App\Mail;

use App\Models\Account;
use App\Services\Email\TemplateResolverService;
use App\Services\GlobalConfigService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

abstract class ApplicationMailable extends Mailable
{
    use Queueable, SerializesModels;

    protected ?Account $account = null;
    protected ?object $agent = null;
    protected ?object $conversation = null;
    protected ?string $actionUrl = null;
    protected ?string $attachmentUrl = null;
    protected array $failedContacts = [];
    protected array $importedContacts = [];

    /**
     * Create a new message instance.
     */
    public function __construct(?Account $account = null)
    {
        $this->account = $account;
        $this->setLocale();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->getFromAddress(),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: $this->getViewName(),
            with: $this->getViewData(),
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Send mail with liquid template support and error handling.
     */
    protected function sendMailWithLiquid(array $options): static
    {
        try {
            Log::info("Email sent to {$options['to']} with subject {$options['subject']}");
            
            return $this->to($options['to'])
                        ->subject($options['subject']);
        } catch (\Exception $e) {
            $this->handleSmtpExceptions($e);
            return $this;
        }
    }

    /**
     * Get liquid droppables for template variables.
     */
    protected function getLiquidDroppables(): array
    {
        return [
            'account' => $this->account,
            'user' => $this->agent,
            'conversation' => $this->conversation,
            'inbox' => $this->conversation?->inbox,
        ];
    }

    /**
     * Get liquid locals for template variables.
     */
    protected function getLiquidLocals(): array
    {
        $locals = [
            'global_config' => $this->getGlobalConfig(),
            'action_url' => $this->actionUrl,
        ];

        if ($this->attachmentUrl) {
            $locals['attachment_url'] = $this->attachmentUrl;
        }

        if (!empty($this->failedContacts) || !empty($this->importedContacts)) {
            $locals['failed_contacts'] = $this->failedContacts;
            $locals['imported_contacts'] = $this->importedContacts;
        }

        return $locals;
    }

    /**
     * Get global configuration.
     */
    protected function getGlobalConfig(): array
    {
        $globalConfig = app(GlobalConfigService::class)->get(['BRAND_NAME', 'BRAND_URL']);
        
        // Provide fallbacks from email and app config
        return [
            'BRAND_NAME' => $globalConfig['BRAND_NAME'] ?? config('email.brand.name', config('app.name', 'Chatwoot')),
            'BRAND_URL' => $globalConfig['BRAND_URL'] ?? config('email.brand.url', config('app.url')),
        ];
    }

    /**
     * Get from address based on configuration.
     */
    protected function getFromAddress(): string
    {
        return config('mail.from.address', 
            config('email.brand.support_email', 
                'noreply@' . parse_url(config('app.url'), PHP_URL_HOST)
            )
        );
    }

    /**
     * Set locale based on account settings.
     */
    protected function setLocale(): void
    {
        if ($this->account && $this->account->locale) {
            $availableLocales = config('app.available_locales', ['en']);
            if (in_array($this->account->locale, $availableLocales)) {
                App::setLocale($this->account->locale);
            }
        }
    }

    /**
     * Handle SMTP exceptions gracefully.
     */
    protected function handleSmtpExceptions(\Exception $exception): void
    {
        Log::warning('Failed to send Email');
        Log::error("Exception: {$exception->getMessage()}");
    }

    /**
     * Check if SMTP is configured or in development.
     */
    protected function smtpConfigSetOrDevelopment(): bool
    {
        return !empty(config('mail.mailers.smtp.host')) || app()->environment('local');
    }

    /**
     * Get view name for the email template.
     */
    abstract protected function getViewName(): string;

    /**
     * Get view data for the email template.
     */
    protected function getViewData(): array
    {
        return array_merge(
            $this->getLiquidDroppables(),
            $this->getLiquidLocals()
        );
    }
}