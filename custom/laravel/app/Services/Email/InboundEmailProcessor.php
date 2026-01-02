<?php

namespace App\Services\Email;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\Message;
use App\Services\Channels\InboundMessageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InboundEmailProcessor
{
    protected InboundMessageService $inboundMessageService;

    public function __construct(InboundMessageService $inboundMessageService)
    {
        $this->inboundMessageService = $inboundMessageService;
    }

    /**
     * Process inbound email with transaction-based processing.
     */
    public function process(array $emailData): array
    {
        try {
            return DB::transaction(function () use ($emailData) {
                // Validate email data
                $validatedData = $this->validateEmailData($emailData);
                
                // Find or create conversation
                $conversation = $this->findOrCreateConversation($validatedData);
                
                // Create message
                $message = $this->createMessage($conversation, $validatedData);
                
                // Process attachments
                $this->processAttachments($message, $validatedData['attachments'] ?? []);
                
                Log::info('Inbound email processed successfully', [
                    'conversation_id' => $conversation->id,
                    'message_id' => $message->id,
                    'from' => $validatedData['from'],
                ]);

                return [
                    'success' => true,
                    'conversation' => $conversation,
                    'message' => $message,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Inbound email processing failed', [
                'error' => $e->getMessage(),
                'email_data' => $emailData,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate inbound email data.
     */
    protected function validateEmailData(array $emailData): array
    {
        $required = ['from', 'to', 'subject', 'body'];
        
        foreach ($required as $field) {
            if (empty($emailData[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }

        // Validate email addresses
        if (!filter_var($emailData['from'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid from email address: {$emailData['from']}");
        }

        if (!filter_var($emailData['to'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid to email address: {$emailData['to']}");
        }

        // Handle malformed headers
        $emailData = $this->handleMalformedHeaders($emailData);

        return $emailData;
    }

    /**
     * Handle malformed email headers.
     */
    protected function handleMalformedHeaders(array $emailData): array
    {
        // Clean up subject line
        if (isset($emailData['subject'])) {
            $emailData['subject'] = $this->cleanSubject($emailData['subject']);
        }

        // Clean up body content
        if (isset($emailData['body'])) {
            $emailData['body'] = $this->cleanBodyContent($emailData['body']);
        }

        // Handle encoding issues
        foreach (['from', 'to', 'subject'] as $field) {
            if (isset($emailData[$field])) {
                $emailData[$field] = $this->handleEncoding($emailData[$field]);
            }
        }

        return $emailData;
    }

    /**
     * Find or create conversation based on email data.
     */
    protected function findOrCreateConversation(array $emailData): Conversation
    {
        // Try to find existing conversation using UUID pattern
        $conversation = $this->findConversationByUuid($emailData['to']);
        
        if ($conversation) {
            return $conversation;
        }

        // Try to find conversation by subject and contact
        $conversation = $this->findConversationBySubjectAndContact($emailData);
        
        if ($conversation) {
            return $conversation;
        }

        // Create new conversation
        return $this->createNewConversation($emailData);
    }

    /**
     * Find conversation by UUID pattern in email address.
     */
    protected function findConversationByUuid(string $toEmail): ?Conversation
    {
        // Extract UUID from email pattern like: reply+uuid@domain.com
        if (preg_match('/reply\+([a-f0-9-]{36})@/', $toEmail, $matches)) {
            $uuid = $matches[1];
            
            return Conversation::where('uuid', $uuid)->first();
        }

        return null;
    }

    /**
     * Find conversation by subject and contact.
     */
    protected function findConversationBySubjectAndContact(array $emailData): ?Conversation
    {
        // Extract conversation ID from subject if present
        if (preg_match('/\[.*#(\d+)\]/', $emailData['subject'], $matches)) {
            $displayId = $matches[1];
            
            $conversation = Conversation::where('display_id', $displayId)->first();
            
            if ($conversation && $this->isContactMatch($conversation, $emailData['from'])) {
                return $conversation;
            }
        }

        return null;
    }

    /**
     * Create new conversation for inbound email.
     */
    protected function createNewConversation(array $emailData): Conversation
    {
        // Find inbox for email
        $inbox = $this->findInboxForEmail($emailData['to']);
        
        if (!$inbox) {
            throw new \Exception("No inbox found for email: {$emailData['to']}");
        }

        // Find or create contact
        $contact = $this->findOrCreateContact($emailData['from'], $inbox->account);

        // Create conversation
        $conversation = Conversation::create([
            'account_id' => $inbox->account_id,
            'inbox_id' => $inbox->id,
            'contact_id' => $contact->id,
            'status' => 'open',
            'uuid' => Str::uuid(),
            'identifier' => null,
        ]);

        return $conversation;
    }

    /**
     * Find inbox for email address.
     */
    protected function findInboxForEmail(string $email): ?Inbox
    {
        // Try to find inbox by email configuration
        return Inbox::whereHas('channel', function ($query) use ($email) {
            $query->where('channel_type', 'Channel::Email')
                  ->where(function ($q) use ($email) {
                      $q->whereJsonContains('settings->email', $email)
                        ->orWhereJsonContains('settings->imap_email', $email)
                        ->orWhereJsonContains('settings->smtp_email', $email);
                  });
        })->first();
    }

    /**
     * Find or create contact.
     */
    protected function findOrCreateContact(string $email, Account $account): Contact
    {
        $contact = Contact::where('account_id', $account->id)
            ->where('email', $email)
            ->first();

        if (!$contact) {
            $contact = Contact::create([
                'account_id' => $account->id,
                'email' => $email,
                'name' => $this->extractNameFromEmail($email),
            ]);
        }

        return $contact;
    }

    /**
     * Create message from email data.
     */
    protected function createMessage(Conversation $conversation, array $emailData): Message
    {
        return Message::create([
            'account_id' => $conversation->account_id,
            'inbox_id' => $conversation->inbox_id,
            'conversation_id' => $conversation->id,
            'contact_id' => $conversation->contact_id,
            'message_type' => 'incoming',
            'content_type' => 'text',
            'content' => $emailData['body'],
            'source_id' => $emailData['message_id'] ?? null,
            'external_source_ids' => [
                'email_message_id' => $emailData['message_id'] ?? null,
                'email_subject' => $emailData['subject'],
            ],
        ]);
    }

    /**
     * Process email attachments.
     */
    protected function processAttachments(Message $message, array $attachments): void
    {
        foreach ($attachments as $attachment) {
            try {
                // Process attachment based on type
                $this->processAttachment($message, $attachment);
            } catch (\Exception $e) {
                Log::warning('Failed to process email attachment', [
                    'message_id' => $message->id,
                    'attachment' => $attachment,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Process individual attachment.
     */
    protected function processAttachment(Message $message, array $attachment): void
    {
        // This would integrate with your file storage system
        // For now, just log the attachment info
        Log::info('Processing email attachment', [
            'message_id' => $message->id,
            'filename' => $attachment['filename'] ?? 'unknown',
            'content_type' => $attachment['content_type'] ?? 'unknown',
            'size' => $attachment['size'] ?? 0,
        ]);
    }

    /**
     * Check if contact matches conversation.
     */
    protected function isContactMatch(Conversation $conversation, string $email): bool
    {
        return $conversation->contact && $conversation->contact->email === $email;
    }

    /**
     * Extract name from email address.
     */
    protected function extractNameFromEmail(string $email): string
    {
        $parts = explode('@', $email);
        $localPart = $parts[0];
        
        // Convert dots and underscores to spaces and title case
        return Str::title(str_replace(['.', '_', '-'], ' ', $localPart));
    }

    /**
     * Clean subject line.
     */
    protected function cleanSubject(string $subject): string
    {
        // Remove common email prefixes
        $subject = preg_replace('/^(Re:|Fwd?:|RE:|FWD?:)\s*/i', '', $subject);
        
        // Decode MIME encoded words
        $subject = iconv_mime_decode($subject, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
        
        return trim($subject);
    }

    /**
     * Clean body content.
     */
    protected function cleanBodyContent(string $body): string
    {
        // Remove quoted text (lines starting with >)
        $lines = explode("\n", $body);
        $cleanLines = [];
        
        foreach ($lines as $line) {
            if (!preg_match('/^\s*>/', $line)) {
                $cleanLines[] = $line;
            }
        }
        
        return trim(implode("\n", $cleanLines));
    }

    /**
     * Handle text encoding issues.
     */
    protected function handleEncoding(string $text): string
    {
        // Decode MIME encoded words
        $decoded = iconv_mime_decode($text, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
        
        // Ensure UTF-8 encoding
        if (!mb_check_encoding($decoded, 'UTF-8')) {
            $decoded = mb_convert_encoding($decoded, 'UTF-8', 'auto');
        }
        
        return $decoded;
    }
}