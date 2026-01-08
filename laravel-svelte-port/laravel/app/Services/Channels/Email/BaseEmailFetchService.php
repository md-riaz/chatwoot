<?php

namespace App\Services\Channels\Email;

use App\Models\Channels\Email;

/**
 * Base email fetch service providing common functionality.
 * 
 * @see app/services/imap/base_fetch_email_service.rb
 */
abstract class BaseEmailFetchService
{
    protected Email $channel;

    public function __construct(Email $channel)
    {
        $this->channel = $channel;
    }

    /**
     * Abstract method to fetch emails - must be implemented by subclasses.
     */
    abstract public function fetchEmails(string $folder = 'INBOX', int $limit = 50): array;

    /**
     * Parse email message into standardized format.
     */
    protected function parseEmail($message): array
    {
        $attachments = [];
        foreach ($message->getAttachments() as $attachment) {
            $attachments[] = [
                'filename' => $attachment->getName(),
                'mime_type' => $attachment->getMimeType(),
                'size' => $attachment->getSize(),
                'content' => base64_encode($attachment->getContent()),
            ];
        }

        return [
            'uid' => $message->getUid(),
            'message_id' => $message->getMessageId(),
            'subject' => $message->getSubject(),
            'from' => $message->getFrom()[0]->mail ?? null,
            'from_name' => $message->getFrom()[0]->personal ?? null,
            'to' => array_map(fn($t) => $t->mail, $message->getTo()->toArray()),
            'cc' => array_map(fn($c) => $c->mail, $message->getCc()->toArray()),
            'date' => $message->getDate(),
            'body_text' => $message->getTextBody(),
            'body_html' => $message->getHTMLBody(),
            'in_reply_to' => $message->getInReplyTo(),
            'references' => $message->getReferences(),
            'attachments' => $attachments,
        ];
    }
}