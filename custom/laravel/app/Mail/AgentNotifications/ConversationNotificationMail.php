<?php

namespace App\Mail\AgentNotifications;

use App\Mail\ApplicationMailable;
use App\Models\Account;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Mail\Mailables\Envelope;

class ConversationNotificationMail extends ApplicationMailable
{
    protected string $notificationType;
    protected ?Message $message = null;

    public function __construct(
        string $notificationType,
        Conversation $conversation,
        User $agent,
        ?Account $account = null,
        ?Message $message = null
    ) {
        parent::__construct($account);
        
        $this->notificationType = $notificationType;
        $this->conversation = $conversation;
        $this->agent = $agent;
        $this->message = $message;
        
        $this->actionUrl = $this->generateActionUrl();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->getFromAddress(),
            to: [$this->agent->email],
            subject: $this->getSubject(),
        );
    }

    /**
     * Create conversation creation notification.
     */
    public static function conversationCreation(Conversation $conversation, User $agent): static
    {
        return new static('conversation_creation', $conversation, $agent, $conversation->account);
    }

    /**
     * Create conversation assignment notification.
     */
    public static function conversationAssignment(Conversation $conversation, User $agent): static
    {
        return new static('conversation_assignment', $conversation, $agent, $conversation->account);
    }

    /**
     * Create conversation mention notification.
     */
    public static function conversationMention(Conversation $conversation, User $agent, Message $message): static
    {
        return new static('conversation_mention', $conversation, $agent, $conversation->account, $message);
    }

    /**
     * Create assigned conversation new message notification.
     */
    public static function assignedConversationNewMessage(Conversation $conversation, User $agent, Message $message): static
    {
        return new static('assigned_conversation_new_message', $conversation, $agent, $conversation->account, $message);
    }

    /**
     * Create participating conversation new message notification.
     */
    public static function participatingConversationNewMessage(Conversation $conversation, User $agent, Message $message): static
    {
        return new static('participating_conversation_new_message', $conversation, $agent, $conversation->account, $message);
    }

    /**
     * Create SLA missed first response notification.
     */
    public static function slaMissedFirstResponse(Conversation $conversation, User $agent): static
    {
        return new static('sla_missed_first_response', $conversation, $agent, $conversation->account);
    }

    /**
     * Create SLA missed next response notification.
     */
    public static function slaMissedNextResponse(Conversation $conversation, User $agent): static
    {
        return new static('sla_missed_next_response', $conversation, $agent, $conversation->account);
    }

    /**
     * Create SLA missed resolution notification.
     */
    public static function slaMissedResolution(Conversation $conversation, User $agent): static
    {
        return new static('sla_missed_resolution', $conversation, $agent, $conversation->account);
    }

    /**
     * Get email subject based on notification type.
     */
    protected function getSubject(): string
    {
        $agentName = $this->agent->available_name ?? $this->agent->name;
        $conversationId = $this->conversation->display_id;
        $inboxName = $this->conversation->inbox?->name ?? 'Unknown Inbox';

        return match ($this->notificationType) {
            'conversation_creation' => "{$agentName}, A new conversation [ID - {$conversationId}] has been created in {$inboxName}.",
            'conversation_assignment' => "{$agentName}, A new conversation [ID - {$conversationId}] has been assigned to you.",
            'conversation_mention' => "{$agentName}, You have been mentioned in conversation [ID - {$conversationId}]",
            'assigned_conversation_new_message' => "{$agentName}, New message in your assigned conversation [ID - {$conversationId}].",
            'participating_conversation_new_message' => "{$agentName}, New message in your participating conversation [ID - {$conversationId}].",
            'sla_missed_first_response' => "{$agentName}, SLA missed for first response in conversation [ID - {$conversationId}].",
            'sla_missed_next_response' => "{$agentName}, SLA missed for next response in conversation [ID - {$conversationId}].",
            'sla_missed_resolution' => "{$agentName}, SLA missed for resolution in conversation [ID - {$conversationId}].",
            default => "{$agentName}, Notification for conversation [ID - {$conversationId}].",
        };
    }

    /**
     * Get view name for the email template.
     */
    protected function getViewName(): string
    {
        return "emails.agent-notifications.{$this->notificationType}";
    }

    /**
     * Get view data for the email template.
     */
    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'notification_type' => $this->notificationType,
            'message' => $this->message,
        ]);
    }

    /**
     * Generate action URL for the conversation.
     */
    protected function generateActionUrl(): string
    {
        $baseUrl = config('app.frontend_url', config('app.url'));
        return "{$baseUrl}/app/accounts/{$this->conversation->account_id}/conversations/{$this->conversation->display_id}";
    }

    /**
     * Get liquid droppables for template variables.
     */
    protected function getLiquidDroppables(): array
    {
        return array_merge(parent::getLiquidDroppables(), [
            'message' => $this->message,
        ]);
    }
}