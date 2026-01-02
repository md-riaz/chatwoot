<?php

namespace App\Mail;

use App\Mail\ApplicationMailable;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Mail\Mailables\Envelope;

class ConversationTranscriptMailable extends ApplicationMailable
{
    protected string $emailType;
    protected ?Message $message = null;
    protected bool $withSummary = false;
    protected array $ccEmails = [];
    protected array $bccEmails = [];

    public function __construct(
        Conversation $conversation,
        string $emailType = 'conversation_transcript',
        ?Message $message = null,
        bool $withSummary = false,
        array $ccEmails = [],
        array $bccEmails = []
    ) {
        parent::__construct($conversation->account);
        
        $this->conversation = $conversation;
        $this->emailType = $emailType;
        $this->message = $message;
        $this->withSummary = $withSummary;
        $this->ccEmails = $ccEmails;
        $this->bccEmails = $bccEmails;
        
        $this->actionUrl = $this->generateActionUrl();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $envelope = new Envelope(
            from: $this->getFromAddress(),
            to: [$this->getRecipientEmail()],
            subject: $this->getSubject(),
        );

        // Add CC and BCC if specified
        if (!empty($this->ccEmails)) {
            $envelope->cc($this->ccEmails);
        }

        if (!empty($this->bccEmails)) {
            $envelope->bcc($this->bccEmails);
        }

        return $envelope;
    }

    /**
     * Create reply with summary email.
     */
    public static function replyWithSummary(
        Conversation $conversation,
        Message $message,
        array $ccEmails = [],
        array $bccEmails = []
    ): static {
        return new static($conversation, 'reply_with_summary', $message, true, $ccEmails, $bccEmails);
    }

    /**
     * Create reply without summary email.
     */
    public static function replyWithoutSummary(
        Conversation $conversation,
        Message $message,
        array $ccEmails = [],
        array $bccEmails = []
    ): static {
        return new static($conversation, 'reply_without_summary', $message, false, $ccEmails, $bccEmails);
    }

    /**
     * Create email reply.
     */
    public static function emailReply(
        Conversation $conversation,
        Message $message,
        array $ccEmails = [],
        array $bccEmails = []
    ): static {
        return new static($conversation, 'email_reply', $message, false, $ccEmails, $bccEmails);
    }

    /**
     * Create conversation transcript email.
     */
    public static function conversationTranscript(
        Conversation $conversation,
        array $ccEmails = [],
        array $bccEmails = []
    ): static {
        return new static($conversation, 'conversation_transcript', null, false, $ccEmails, $bccEmails);
    }

    /**
     * Get email subject based on type.
     */
    protected function getSubject(): string
    {
        $conversationId = $this->conversation->display_id ?? $this->conversation->id;
        $inboxName = $this->conversation->inbox?->name ?? 'Support';
        $brandName = $this->getGlobalConfig()['BRAND_NAME'];

        return match ($this->emailType) {
            'reply_with_summary', 'reply_without_summary' => 
                "Re: [{$inboxName}] Conversation #{$conversationId} - {$brandName}",
            'email_reply' => 
                "Re: [{$inboxName}] #{$conversationId} - {$brandName}",
            'conversation_transcript' => 
                "Conversation transcript - #{$conversationId} - {$brandName}",
            default => 
                "Conversation #{$conversationId} - {$brandName}",
        };
    }

    /**
     * Get view name for the email template.
     */
    protected function getViewName(): string
    {
        return "emails.conversation.{$this->emailType}";
    }

    /**
     * Get view data for the email template.
     */
    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'email_type' => $this->emailType,
            'message' => $this->message,
            'with_summary' => $this->withSummary,
            'messages' => $this->getConversationMessages(),
            'attachments' => $this->getMessageAttachments(),
        ]);
    }

    /**
     * Get recipient email address.
     */
    protected function getRecipientEmail(): string
    {
        // For email replies, use the contact's email
        if ($this->conversation->contact && $this->conversation->contact->email) {
            return $this->conversation->contact->email;
        }

        // Fallback to a default email or throw exception
        throw new \Exception('No recipient email found for conversation');
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
     * Get conversation messages for transcript.
     */
    protected function getConversationMessages(): array
    {
        return $this->conversation->messages()
            ->with(['user', 'attachments'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Get message attachments.
     */
    protected function getMessageAttachments(): array
    {
        if (!$this->message) {
            return [];
        }

        return $this->message->attachments()
            ->get()
            ->map(function ($attachment) {
                return [
                    'filename' => $attachment->file_name,
                    'url' => $attachment->file_url,
                    'content_type' => $attachment->content_type,
                    'file_size' => $attachment->file_size,
                ];
            })
            ->toArray();
    }

    /**
     * Get custom message ID for email threading.
     */
    public function getMessageId(): string
    {
        $conversationId = $this->conversation->id;
        $messageId = $this->message?->id ?? 'transcript';
        $domain = parse_url(config('app.url'), PHP_URL_HOST) ?? strtolower(config('app.name', 'chatwoot')) . '.com';
        
        return "<conversation-{$conversationId}-message-{$messageId}@{$domain}>";
    }

    /**
     * Get In-Reply-To header for email threading.
     */
    public function getInReplyTo(): ?string
    {
        if (!$this->message) {
            return null;
        }

        // Get the previous message in the conversation
        $previousMessage = $this->conversation->messages()
            ->where('id', '<', $this->message->id)
            ->orderBy('id', 'desc')
            ->first();

        if ($previousMessage) {
            $domain = parse_url(config('app.url'), PHP_URL_HOST) ?? strtolower(config('app.name', 'chatwoot')) . '.com';
            return "<conversation-{$this->conversation->id}-message-{$previousMessage->id}@{$domain}>";
        }

        return null;
    }

    /**
     * Get References header for email threading.
     */
    public function getReferences(): array
    {
        $references = [];
        $domain = parse_url(config('app.url'), PHP_URL_HOST) ?? strtolower(config('app.name', 'chatwoot')) . '.com';
        
        // Add conversation root reference
        $references[] = "<conversation-{$this->conversation->id}@{$domain}>";
        
        // Add previous messages in thread
        if ($this->message) {
            $previousMessages = $this->conversation->messages()
                ->where('id', '<', $this->message->id)
                ->orderBy('id', 'asc')
                ->limit(10) // Limit to avoid too long headers
                ->get();

            foreach ($previousMessages as $msg) {
                $references[] = "<conversation-{$this->conversation->id}-message-{$msg->id}@{$domain}>";
            }
        }

        return $references;
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
