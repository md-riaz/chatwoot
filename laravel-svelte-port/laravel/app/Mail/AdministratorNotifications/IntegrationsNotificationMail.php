<?php

namespace App\Mail\AdministratorNotifications;

use App\Mail\ApplicationMailable;
use App\Models\Account;
use App\Models\Integrations\Hook;
use Illuminate\Mail\Mailables\Envelope;

class IntegrationsNotificationMail extends ApplicationMailable
{
    protected string $notificationType;
    protected ?Hook $integration = null;
    protected array $metadata = [];
    protected ?string $customTo = null;

    public function __construct(
        string $notificationType,
        ?Hook $integration = null,
        ?Account $account = null,
        array $metadata = [],
        ?string $customTo = null
    ) {
        parent::__construct($account ?? $integration?->account);
        
        $this->notificationType = $notificationType;
        $this->integration = $integration;
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
     * Create Dialogflow disconnect notification.
     */
    public static function dialogflowDisconnect(Hook $integration, string $to): static
    {
        return new static('dialogflow_disconnect', $integration, null, [
            'integration_name' => 'Dialogflow',
            'app_id' => $integration->app_id,
        ], $to);
    }

    /**
     * Create Slack disconnect notification.
     */
    public static function slackDisconnect(Hook $integration, string $to): static
    {
        return new static('slack_disconnect', $integration, null, [
            'integration_name' => 'Slack',
            'app_id' => $integration->app_id,
        ], $to);
    }

    /**
     * Create integration connection failure notification.
     */
    public static function integrationConnectionFailure(string $integrationType, Account $account, string $error, string $to): static
    {
        return new static('integration_connection_failure', null, $account, [
            'integration_name' => $integrationType,
            'error_message' => $error,
        ], $to);
    }

    /**
     * Create integration quota exceeded notification.
     */
    public static function integrationQuotaExceeded(string $integrationType, Account $account, string $quotaType, string $to): static
    {
        return new static('integration_quota_exceeded', null, $account, [
            'integration_name' => $integrationType,
            'quota_type' => $quotaType,
        ], $to);
    }

    /**
     * Create integration configuration change notification.
     */
    public static function integrationConfigurationChange(Hook $integration, array $changes, string $to): static
    {
        return new static('integration_configuration_change', $integration, null, [
            'integration_name' => $integration->app_id,
            'changes' => $changes,
        ], $to);
    }

    /**
     * Create integration deprecation notice.
     */
    public static function integrationDeprecationNotice(string $integrationType, Account $account, string $deprecationDate, string $to): static
    {
        return new static('integration_deprecation_notice', null, $account, [
            'integration_name' => $integrationType,
            'deprecation_date' => $deprecationDate,
        ], $to);
    }

    /**
     * Get email subject based on notification type.
     */
    protected function getSubject(): string
    {
        $integrationName = $this->metadata['integration_name'] ?? 'Integration';

        return match ($this->notificationType) {
            'dialogflow_disconnect' => "Dialogflow integration disconnected",
            'slack_disconnect' => "Slack integration disconnected",
            'integration_connection_failure' => "{$integrationName} integration connection failed",
            'integration_quota_exceeded' => "{$integrationName} integration quota exceeded",
            'integration_configuration_change' => "{$integrationName} integration configuration changed",
            'integration_deprecation_notice' => "{$integrationName} integration deprecation notice",
            default => "Integration notification: {$integrationName}",
        };
    }

    /**
     * Get view name for the email template.
     */
    protected function getViewName(): string
    {
        return "emails.administrator-notifications.integrations.{$this->notificationType}";
    }

    /**
     * Get view data for the email template.
     */
    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'notification_type' => $this->notificationType,
            'integration' => $this->integration,
            'metadata' => $this->metadata,
        ]);
    }

    /**
     * Generate action URL based on notification type.
     */
    protected function generateActionUrl(): ?string
    {
        if (!$this->account) {
            return null;
        }

        $baseUrl = config('app.frontend_url', config('app.url'));
        
        return match ($this->notificationType) {
            'dialogflow_disconnect', 'slack_disconnect' => 
                "{$baseUrl}/app/accounts/{$this->account->id}/settings/integrations",
            'integration_connection_failure', 'integration_quota_exceeded', 
            'integration_configuration_change', 'integration_deprecation_notice' => 
                "{$baseUrl}/app/accounts/{$this->account->id}/settings/integrations",
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
                ->wherePivot('role', 1) // 1 = administrator
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
            'integration' => $this->integration,
        ]);
    }
}