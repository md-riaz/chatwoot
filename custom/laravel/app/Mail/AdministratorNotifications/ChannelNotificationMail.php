<?php

namespace App\Mail\AdministratorNotifications;

use App\Mail\ApplicationMailable;
use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Mail\Mailables\Envelope;

class ChannelNotificationMail extends ApplicationMailable
{
    protected string $notificationType;
    protected ?Inbox $inbox = null;
    protected array $metadata = [];
    protected ?string $customTo = null;

    public function __construct(
        string $notificationType,
        ?Inbox $inbox = null,
        ?Account $account = null,
        array $metadata = [],
        ?string $customTo = null
    ) {
        parent::__construct($account ?? $inbox?->account);
        
        $this->notificationType = $notificationType;
        $this->inbox = $inbox;
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
     * Create email disconnect notification.
     */
    public static function emailDisconnect(Inbox $inbox, string $to): static
    {
        return new static('email_disconnect', $inbox, null, [
            'inbox_name' => $inbox->name,
            'channel_type' => 'Email',
        ], $to);
    }

    /**
     * Create Facebook disconnect notification.
     */
    public static function facebookDisconnect(Inbox $inbox, string $to): static
    {
        return new static('facebook_disconnect', $inbox, null, [
            'inbox_name' => $inbox->name,
            'channel_type' => 'Facebook',
        ], $to);
    }

    /**
     * Create Instagram disconnect notification.
     */
    public static function instagramDisconnect(Inbox $inbox, string $to): static
    {
        return new static('instagram_disconnect', $inbox, null, [
            'inbox_name' => $inbox->name,
            'channel_type' => 'Instagram',
        ], $to);
    }

    /**
     * Create WhatsApp disconnect notification.
     */
    public static function whatsappDisconnect(Inbox $inbox, string $to): static
    {
        return new static('whatsapp_disconnect', $inbox, null, [
            'inbox_name' => $inbox->name,
            'channel_type' => 'WhatsApp',
        ], $to);
    }

    /**
     * Create channel reauthorization notification.
     */
    public static function channelReauthorizationRequired(Inbox $inbox, string $to): static
    {
        return new static('channel_reauthorization_required', $inbox, null, [
            'inbox_name' => $inbox->name,
            'channel_type' => $inbox->channel_type,
        ], $to);
    }

    /**
     * Create channel configuration alert.
     */
    public static function channelConfigurationAlert(Inbox $inbox, string $message, string $to): static
    {
        return new static('channel_configuration_alert', $inbox, null, [
            'inbox_name' => $inbox->name,
            'channel_type' => $inbox->channel_type,
            'alert_message' => $message,
        ], $to);
    }

    /**
     * Create channel webhook failure notification.
     */
    public static function channelWebhookFailure(Inbox $inbox, string $error, string $to): static
    {
        return new static('channel_webhook_failure', $inbox, null, [
            'inbox_name' => $inbox->name,
            'channel_type' => $inbox->channel_type,
            'error_message' => $error,
        ], $to);
    }

    /**
     * Create channel quota exceeded alert.
     */
    public static function channelQuotaExceeded(Inbox $inbox, string $quotaType, string $to): static
    {
        return new static('channel_quota_exceeded', $inbox, null, [
            'inbox_name' => $inbox->name,
            'channel_type' => $inbox->channel_type,
            'quota_type' => $quotaType,
        ], $to);
    }

    /**
     * Get email subject based on notification type.
     */
    protected function getSubject(): string
    {
        $inboxName = $this->inbox?->name ?? 'Unknown Inbox';
        $channelType = $this->metadata['channel_type'] ?? 'Channel';

        return match ($this->notificationType) {
            'email_disconnect' => "Email channel disconnected: {$inboxName}",
            'facebook_disconnect' => "Facebook channel disconnected: {$inboxName}",
            'instagram_disconnect' => "Instagram channel disconnected: {$inboxName}",
            'whatsapp_disconnect' => "WhatsApp channel disconnected: {$inboxName}",
            'channel_reauthorization_required' => "Channel reauthorization required: {$inboxName}",
            'channel_configuration_alert' => "Channel configuration alert: {$inboxName}",
            'channel_webhook_failure' => "Channel webhook failure: {$inboxName}",
            'channel_quota_exceeded' => "Channel quota exceeded: {$inboxName}",
            default => "Channel notification: {$inboxName}",
        };
    }

    /**
     * Get view name for the email template.
     */
    protected function getViewName(): string
    {
        return "emails.administrator-notifications.channel.{$this->notificationType}";
    }

    /**
     * Get view data for the email template.
     */
    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'notification_type' => $this->notificationType,
            'inbox' => $this->inbox,
            'metadata' => $this->metadata,
        ]);
    }

    /**
     * Generate action URL based on notification type.
     */
    protected function generateActionUrl(): ?string
    {
        if (!$this->inbox) {
            return null;
        }

        $baseUrl = config('app.frontend_url', config('app.url'));
        return "{$baseUrl}/app/accounts/{$this->inbox->account_id}/settings/inboxes/{$this->inbox->id}";
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
     * Get liquid locals for template variables.
     */
    protected function getLiquidLocals(): array
    {
        return array_merge(parent::getLiquidLocals(), $this->metadata);
    }

    /**
     * Get liquid droppables for template variables.
     */
    protected function getLiquidDroppables(): array
    {
        return array_merge(parent::getLiquidDroppables(), [
            'inbox' => $this->inbox,
        ]);
    }
}