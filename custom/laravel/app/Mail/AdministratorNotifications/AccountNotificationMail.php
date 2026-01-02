<?php

namespace App\Mail\AdministratorNotifications;

use App\Mail\ApplicationMailable;
use App\Models\Account;
use App\Models\AutomationRule;
use App\Models\DataImport;
use App\Models\User;
use Illuminate\Mail\Mailables\Envelope;

class AccountNotificationMail extends ApplicationMailable
{
    protected string $notificationType;
    protected array $metadata = [];
    protected ?string $customTo = null;

    public function __construct(
        string $notificationType,
        ?Account $account = null,
        array $metadata = [],
        ?string $customTo = null
    ) {
        parent::__construct($account);
        
        $this->notificationType = $notificationType;
        $this->metadata = $metadata;
        $this->customTo = $customTo;
        
        $this->actionUrl = $this->generateActionUrl();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $to = $this->customTo ?? $this->getDefaultRecipient();
        
        return new Envelope(
            from: $this->getFromAddress(),
            to: [$to],
            subject: $this->getSubject(),
        );
    }

    /**
     * Create account deletion user initiated notification.
     */
    public static function accountDeletionUserInitiated(Account $account, string $reason, string $to): static
    {
        $deletionDate = $account->custom_attributes['marked_for_deletion_at'] ?? null;
        
        return new static('account_deletion_user_initiated', $account, [
            'account_name' => $account->name,
            'deletion_date' => static::formatDeletionDate($deletionDate),
            'reason' => $reason,
        ], $to);
    }

    /**
     * Create account deletion for inactivity notification.
     */
    public static function accountDeletionForInactivity(Account $account, string $reason, string $to): static
    {
        $deletionDate = $account->custom_attributes['marked_for_deletion_at'] ?? null;
        
        return new static('account_deletion_for_inactivity', $account, [
            'account_name' => $account->name,
            'deletion_date' => static::formatDeletionDate($deletionDate),
            'reason' => $reason,
        ], $to);
    }

    /**
     * Create contact import complete notification.
     */
    public static function contactImportComplete(DataImport $dataImport, string $to): static
    {
        $actionUrl = null;
        if ($dataImport->failed_records_file) {
            $actionUrl = $dataImport->failed_records_file; // URL to failed records file
        } else {
            $baseUrl = config('app.frontend_url', config('app.url'));
            $actionUrl = "{$baseUrl}/app/accounts/{$dataImport->account_id}/contacts";
        }

        $instance = new static('contact_import_complete', $dataImport->account, [
            'failed_contacts' => $dataImport->total_records - $dataImport->processed_records,
            'imported_contacts' => $dataImport->processed_records,
        ], $to);
        
        $instance->actionUrl = $actionUrl;
        return $instance;
    }

    /**
     * Create contact import failed notification.
     */
    public static function contactImportFailed(Account $account, string $to): static
    {
        return new static('contact_import_failed', $account, [], $to);
    }

    /**
     * Create contact export complete notification.
     */
    public static function contactExportComplete(string $fileUrl, string $to, Account $account): static
    {
        $instance = new static('contact_export_complete', $account, [], $to);
        $instance->actionUrl = $fileUrl;
        return $instance;
    }

    /**
     * Create automation rule disabled notification.
     */
    public static function automationRuleDisabled(AutomationRule $rule, string $to): static
    {
        $baseUrl = config('app.frontend_url', config('app.url'));
        $actionUrl = "{$baseUrl}/app/accounts/{$rule->account_id}/settings/automation/list";
        
        $instance = new static('automation_rule_disabled', $rule->account, [
            'rule_name' => $rule->name,
        ], $to);
        
        $instance->actionUrl = $actionUrl;
        return $instance;
    }

    /**
     * Get email subject based on notification type.
     */
    protected function getSubject(): string
    {
        return match ($this->notificationType) {
            'account_deletion_user_initiated' => 'Your Chatwoot account deletion has been scheduled',
            'account_deletion_for_inactivity' => 'Your Chatwoot account is scheduled for deletion due to inactivity',
            'contact_import_complete' => 'Contact Import Completed',
            'contact_import_failed' => 'Contact Import Failed',
            'contact_export_complete' => "Your contact's export file is available to download.",
            'automation_rule_disabled' => 'Automation rule disabled due to validation errors.',
            default => 'Account Notification',
        };
    }

    /**
     * Get view name for the email template.
     */
    protected function getViewName(): string
    {
        return "emails.administrator-notifications.account.{$this->notificationType}";
    }

    /**
     * Get view data for the email template.
     */
    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'notification_type' => $this->notificationType,
            'metadata' => $this->metadata,
        ]);
    }

    /**
     * Generate action URL based on notification type.
     */
    protected function generateActionUrl(): ?string
    {
        if ($this->actionUrl) {
            return $this->actionUrl;
        }

        $baseUrl = config('app.frontend_url', config('app.url'));
        
        return match ($this->notificationType) {
            'account_deletion_user_initiated', 'account_deletion_for_inactivity' => 
                "{$baseUrl}/app/accounts/{$this->account?->id}/settings/general",
            'automation_rule_disabled' => 
                "{$baseUrl}/app/accounts/{$this->account?->id}/settings/automation/list",
            default => null,
        };
    }

    /**
     * Get default recipient email.
     */
    protected function getDefaultRecipient(): string
    {
        // Get account owner or first admin
        if ($this->account) {
            $owner = $this->account->users()
                ->wherePivot('role', 'administrator')
                ->first();
            
            if ($owner) {
                return $owner->email;
            }
        }

        return config('mail.from.address');
    }

    /**
     * Format deletion date.
     */
    protected static function formatDeletionDate(?string $deletionDateStr): string
    {
        if (empty($deletionDateStr)) {
            return 'Unknown';
        }

        try {
            return \Carbon\Carbon::parse($deletionDateStr)->format('F d, Y');
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get liquid locals for template variables.
     */
    protected function getLiquidLocals(): array
    {
        return array_merge(parent::getLiquidLocals(), $this->metadata);
    }
}