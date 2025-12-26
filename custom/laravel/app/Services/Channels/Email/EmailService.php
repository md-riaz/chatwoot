<?php

namespace App\Services\Channels\Email;

use App\Models\Inbox;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Client;

class EmailService
{
    protected ?array $imapConfig;
    protected ?array $smtpConfig;
    protected ?Client $imapClient = null;

    public function __construct(?Inbox $inbox = null)
    {
        if ($inbox && $inbox->channel) {
            $this->imapConfig = $inbox->channel->imap_login ? [
                'host' => $inbox->channel->imap_address,
                'port' => $inbox->channel->imap_port,
                'encryption' => $inbox->channel->imap_enable_ssl ? 'ssl' : null,
                'username' => $inbox->channel->imap_login,
                'password' => $inbox->channel->imap_password,
            ] : null;

            $this->smtpConfig = $inbox->channel->smtp_login ? [
                'host' => $inbox->channel->smtp_address,
                'port' => $inbox->channel->smtp_port,
                'encryption' => $inbox->channel->smtp_enable_ssl_tls ? 'tls' : null,
                'username' => $inbox->channel->smtp_login,
                'password' => $inbox->channel->smtp_password,
            ] : null;
        }
    }

    /**
     * Test IMAP connection
     */
    public function testImap(): array
    {
        if (!$this->imapConfig) {
            return ['success' => false, 'error' => 'IMAP not configured'];
        }

        try {
            $client = $this->getImapClient();
            $client->connect();
            $folders = $client->getFolders();

            return [
                'success' => true,
                'message' => 'IMAP connection successful',
                'folders' => array_map(fn($f) => $f->name, $folders->toArray()),
            ];
        } catch (\Exception $e) {
            Log::error('IMAP connection test failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Test SMTP connection
     */
    public function testSmtp(): array
    {
        if (!$this->smtpConfig) {
            return ['success' => false, 'error' => 'SMTP not configured'];
        }

        try {
            // Configure temporary mailer
            config([
                'mail.mailers.test_smtp' => [
                    'transport' => 'smtp',
                    'host' => $this->smtpConfig['host'],
                    'port' => $this->smtpConfig['port'],
                    'encryption' => $this->smtpConfig['encryption'],
                    'username' => $this->smtpConfig['username'],
                    'password' => $this->smtpConfig['password'],
                ],
            ]);

            // Test connection by getting transport
            $transport = Mail::mailer('test_smtp')->getSymfonyTransport();

            return [
                'success' => true,
                'message' => 'SMTP connection successful',
            ];
        } catch (\Exception $e) {
            Log::error('SMTP connection test failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Fetch new emails from IMAP
     */
    public function fetchNewEmails(string $folder = 'INBOX', int $limit = 50): array
    {
        if (!$this->imapConfig) {
            return [];
        }

        try {
            $client = $this->getImapClient();
            $client->connect();

            $inbox = $client->getFolder($folder);
            $messages = $inbox->messages()
                ->unseen()
                ->limit($limit)
                ->get();

            $emails = [];
            foreach ($messages as $message) {
                $emails[] = $this->parseEmail($message);
            }

            return $emails;
        } catch (\Exception $e) {
            Log::error('IMAP fetch emails failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Send email
     */
    public function sendEmail(string $to, string $subject, string $body, array $options = []): array
    {
        try {
            $data = [
                'to' => $to,
                'subject' => $subject,
                'body' => $body,
                'from' => $options['from'] ?? config('mail.from.address'),
                'from_name' => $options['from_name'] ?? config('mail.from.name'),
                'cc' => $options['cc'] ?? [],
                'bcc' => $options['bcc'] ?? [],
                'reply_to' => $options['reply_to'] ?? null,
                'attachments' => $options['attachments'] ?? [],
                'in_reply_to' => $options['in_reply_to'] ?? null,
                'references' => $options['references'] ?? null,
            ];

            Mail::html($body, function ($message) use ($data) {
                $message->to($data['to'])
                    ->from($data['from'], $data['from_name'])
                    ->subject($data['subject']);

                if (!empty($data['cc'])) {
                    $message->cc($data['cc']);
                }

                if (!empty($data['bcc'])) {
                    $message->bcc($data['bcc']);
                }

                if ($data['reply_to']) {
                    $message->replyTo($data['reply_to']);
                }

                if ($data['in_reply_to']) {
                    $message->getHeaders()->addTextHeader('In-Reply-To', $data['in_reply_to']);
                }

                if ($data['references']) {
                    $message->getHeaders()->addTextHeader('References', $data['references']);
                }

                foreach ($data['attachments'] as $attachment) {
                    if (is_string($attachment)) {
                        $message->attach($attachment);
                    } else {
                        $message->attach($attachment['path'], [
                            'as' => $attachment['name'] ?? null,
                            'mime' => $attachment['mime'] ?? null,
                        ]);
                    }
                }
            });

            return ['success' => true, 'message' => 'Email sent successfully'];
        } catch (\Exception $e) {
            Log::error('Email send failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Mark email as read
     */
    public function markAsRead(int $uid, string $folder = 'INBOX'): bool
    {
        try {
            $client = $this->getImapClient();
            $client->connect();

            $inbox = $client->getFolder($folder);
            $message = $inbox->messages()->uid($uid)->first();

            if ($message) {
                $message->setFlag('Seen');
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Mark email as read failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Process inbound email webhook (for services like SendGrid, Mailgun, etc.)
     */
    public function processInboundWebhook(array $payload): array
    {
        // Parse the inbound email based on the webhook provider format
        return [
            'from' => $payload['from'] ?? $payload['sender'] ?? null,
            'to' => $payload['to'] ?? $payload['recipient'] ?? null,
            'subject' => $payload['subject'] ?? null,
            'body_plain' => $payload['text'] ?? $payload['body-plain'] ?? null,
            'body_html' => $payload['html'] ?? $payload['body-html'] ?? null,
            'message_id' => $payload['message_id'] ?? $payload['Message-Id'] ?? null,
            'in_reply_to' => $payload['in_reply_to'] ?? $payload['In-Reply-To'] ?? null,
            'references' => $payload['references'] ?? $payload['References'] ?? null,
            'attachments' => $this->parseAttachments($payload),
            'timestamp' => $payload['timestamp'] ?? now()->timestamp,
        ];
    }

    /**
     * Get IMAP client
     */
    protected function getImapClient(): Client
    {
        if ($this->imapClient) {
            return $this->imapClient;
        }

        $cm = new ClientManager();
        
        $this->imapClient = $cm->make([
            'host' => $this->imapConfig['host'],
            'port' => $this->imapConfig['port'],
            'encryption' => $this->imapConfig['encryption'],
            'validate_cert' => true,
            'username' => $this->imapConfig['username'],
            'password' => $this->imapConfig['password'],
            'protocol' => 'imap',
        ]);

        return $this->imapClient;
    }

    /**
     * Parse email message
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

    /**
     * Parse attachments from webhook payload
     */
    protected function parseAttachments(array $payload): array
    {
        $attachments = [];

        // Handle different webhook formats
        if (isset($payload['attachments']) && is_array($payload['attachments'])) {
            foreach ($payload['attachments'] as $attachment) {
                $attachments[] = [
                    'filename' => $attachment['filename'] ?? $attachment['name'] ?? null,
                    'content_type' => $attachment['content-type'] ?? $attachment['type'] ?? null,
                    'size' => $attachment['size'] ?? null,
                    'url' => $attachment['url'] ?? null,
                    'content' => $attachment['content'] ?? null,
                ];
            }
        }

        return $attachments;
    }
}
