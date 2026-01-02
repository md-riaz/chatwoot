<?php

namespace App\Mail\TeamNotifications;

use App\Mail\ApplicationMailable;
use App\Models\Account;
use App\Models\AutomationRule;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Team;
use Illuminate\Mail\Mailables\Envelope;

class AutomationNotificationMail extends ApplicationMailable
{
    protected string $notificationType;
    protected ?AutomationRule $automationRule = null;
    protected ?Team $team = null;
    protected ?Message $message = null;
    protected array $metadata = [];
    protected array $recipients = [];

    public function __construct(
        string $notificationType,
        ?AutomationRule $automationRule = null,
        ?Conversation $conversation = null,
        ?Team $team = null,
        ?Message $message = null,
        array $recipients = [],
        array $metadata = []
    ) {
        parent::__construct($conversation?->account ?? $automationRule?->account);
        
        $this->notificationType = $notificationType;
        $this->automationRule = $automationRule;
        $this->conversation = $conversation;
        $this->team = $team;
        $this->message = $message;
        $this->recipients = $recipients;
        $this->metadata = $metadata;
        
        $this->actionUrl = $this->generateActionUrl();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $recipients = !empty($this->recipients) ? $this->recipients : $this->getDefaultRecipients();
        
        return new Envelope(
            from: $this->getFromAddress(),
            to: $recipients,
            subject: $this->getSubject(),
        );
    }

    /**
     * Create conversation creation automation notification.
     */
    public static function conversationCreation(
        AutomationRule $rule,
        Conversation $conversation,
        ?Team $team = null,
        array $recipients = []
    ): static {
        return new static(
            'conversation_creation',
            $rule,
            $conversation,
            $team,
            null,
            $recipients,
            [
                'rule_name' => $rule->name,
                'conversation_id' => $conversation->display_id,
                'inbox_name' => $conversation->inbox?->name,
            ]
        );
    }

    /**
     * Create conversation updated automation notification.
     */
    public static function conversationUpdated(
        AutomationRule $rule,
        Conversation $conversation,
        ?Team $team = null,
        array $recipients = [],
        array $changes = []
    ): static {
        return new static(
            'conversation_updated',
            $rule,
            $conversation,
            $team,
            null,
            $recipients,
            [
                'rule_name' => $rule->name,
                'conversation_id' => $conversation->display_id,
                'inbox_name' => $conversation->inbox?->name,
                'changes' => $changes,
            ]
        );
    }

    /**
     * Create message created automation notification.
     */
    public static function messageCreated(
        AutomationRule $rule,
        Message $message,
        ?Team $team = null,
        array $recipients = []
    ): static {
        return new static(
            'message_created',
            $rule,
            $message->conversation,
            $team,
            $message,
            $recipients,
            [
                'rule_name' => $rule->name,
                'conversation_id' => $message->conversation->display_id,
                'inbox_name' => $message->conversation->inbox?->name,
                'message_content' => substr($message->content, 0, 100),
            ]
        );
    }

    /**
     * Create automation rule execution notification.
     */
    public static function automationRuleExecution(
        AutomationRule $rule,
        Conversation $conversation,
        array $actions,
        array $recipients = []
    ): static {
        return new static(
            'automation_rule_execution',
            $rule,
            $conversation,
            null,
            null,
            $recipients,
            [
                'rule_name' => $rule->name,
                'conversation_id' => $conversation->display_id,
                'actions_executed' => $actions,
            ]
        );
    }

    /**
     * Create automation rule failure notification.
     */
    public static function automationRuleFailure(
        AutomationRule $rule,
        Conversation $conversation,
        string $error,
        array $recipients = []
    ): static {
        return new static(
            'automation_rule_failure',
            $rule,
            $conversation,
            null,
            null,
            $recipients,
            [
                'rule_name' => $rule->name,
                'conversation_id' => $conversation->display_id,
                'error_message' => $error,
            ]
        );
    }

    /**
     * Create team assignment notification.
     */
    public static function teamAssignment(
        Team $team,
        Conversation $conversation,
        array $recipients = []
    ): static {
        return new static(
            'team_assignment',
            null,
            $conversation,
            $team,
            null,
            $recipients,
            [
                'team_name' => $team->name,
                'conversation_id' => $conversation->display_id,
                'inbox_name' => $conversation->inbox?->name,
            ]
        );
    }

    /**
     * Get email subject based on notification type.
     */
    protected function getSubject(): string
    {
        $conversationId = $this->conversation?->display_id;
        $ruleName = $this->automationRule?->name;
        $teamName = $this->team?->name;

        return match ($this->notificationType) {
            'conversation_creation' => "Automation triggered: New conversation [ID - {$conversationId}] created by rule '{$ruleName}'",
            'conversation_updated' => "Automation triggered: Conversation [ID - {$conversationId}] updated by rule '{$ruleName}'",
            'message_created' => "Automation triggered: New message in conversation [ID - {$conversationId}] by rule '{$ruleName}'",
            'automation_rule_execution' => "Automation rule '{$ruleName}' executed for conversation [ID - {$conversationId}]",
            'automation_rule_failure' => "Automation rule '{$ruleName}' failed for conversation [ID - {$conversationId}]",
            'team_assignment' => "Team '{$teamName}' assigned to conversation [ID - {$conversationId}]",
            default => "Team automation notification",
        };
    }

    /**
     * Get view name for the email template.
     */
    protected function getViewName(): string
    {
        return "emails.team-notifications.automation.{$this->notificationType}";
    }

    /**
     * Get view data for the email template.
     */
    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'notification_type' => $this->notificationType,
            'automation_rule' => $this->automationRule,
            'team' => $this->team,
            'message' => $this->message,
            'metadata' => $this->metadata,
        ]);
    }

    /**
     * Generate action URL based on notification type.
     */
    protected function generateActionUrl(): ?string
    {
        if (!$this->conversation) {
            return null;
        }

        $baseUrl = config('app.frontend_url', config('app.url'));
        return "{$baseUrl}/app/accounts/{$this->conversation->account_id}/conversations/{$this->conversation->display_id}";
    }

    /**
     * Get default recipients for team notifications.
     */
    protected function getDefaultRecipients(): array
    {
        $recipients = [];

        // Get team members if team is specified
        if ($this->team) {
            $teamMembers = $this->team->users()->get();
            foreach ($teamMembers as $member) {
                $recipients[] = $member->email;
            }
        }

        // Get account administrators if no team specified
        if (empty($recipients) && $this->account) {
            $admins = $this->account->users()
                ->wherePivot('role', 'administrator')
                ->get();
            
            foreach ($admins as $admin) {
                $recipients[] = $admin->email;
            }
        }

        // Fallback to default email
        if (empty($recipients)) {
            $recipients[] = config('mail.from.address');
        }

        return array_unique($recipients);
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
            'automation_rule' => $this->automationRule,
            'team' => $this->team,
            'message' => $this->message,
        ]);
    }
}