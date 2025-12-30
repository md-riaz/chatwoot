<?php

namespace App\Jobs\Channels;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;

class ProcessInboundEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $email;

    public function __construct(array $email)
    {
        // Expected keys: message_id, from, to, subject, body, attachments (optional)
        $this->email = $email;
    }

    public function handle(): void
    {
        try {
            $messageId = $this->email['message_id'] ?? null;
            $to = $this->email['to'] ?? null;

            if (! $to) {
                Log::warning('ProcessInboundEmailJob: missing recipient', ['email' => $this->email]);
                return;
            }

            // Resolve inbox by recipient address (simple heuristic)
            $inbox = \App\Models\Inbox::where('channel_type', 'Channel::Email')
                ->whereJsonContains('channel', ['email' => $to])
                ->first();

            if (! $inbox) {
                Log::warning('ProcessInboundEmailJob: no inbox for recipient', ['to' => $to]);
                return;
            }

            // Idempotency: skip if message with same external_source_id exists
            if ($messageId && DB::table('messages')->where('external_source_id', $messageId)->exists()) {
                Log::info('ProcessInboundEmailJob: duplicate message, skipping', ['message_id' => $messageId]);
                return;
            }

            // Find or create contact by from email
            $from = $this->email['from'] ?? null;
            $contact = null;
            if ($from) {
                $contact = \App\Models\Contact::firstOrCreate([
                    'account_id' => $inbox->account_id,
                    'identifier' => 'email:' . strtolower($from),
                ], [
                    'name' => $this->email['from_name'] ?? null,
                    'source' => 'email',
                ]);
            }

            // Find or create conversation
            $conversation = null;
            if ($contact) {
                $conversation = \App\Models\Conversation::where('account_id', $inbox->account_id)
                    ->where('inbox_id', $inbox->id)
                    ->where('contact_id', $contact->id)
                    ->where('status', \App\Models\Conversation::STATUS_OPEN)
                    ->first();
            }

            if (! $conversation) {
                $conversation = \App\Models\Conversation::create([
                    'account_id' => $inbox->account_id,
                    'inbox_id' => $inbox->id,
                    'contact_id' => $contact?->id ?? null,
                    'status' => \App\Models\Conversation::STATUS_OPEN,
                ]);

                try {
                    event(new \App\Events\Conversation\ConversationCreated($conversation));
                } catch (\Throwable $e) {
                    Log::warning('ProcessInboundEmailJob: event dispatch failed', ['error' => $e->getMessage()]);
                }
            }

            // Create message
            $msg = \App\Models\Message::create([
                'account_id' => $inbox->account_id,
                'conversation_id' => $conversation->id,
                'inbox_id' => $inbox->id,
                'sender_id' => $contact?->id,
                'sender_type' => $contact ? \App\Models\Contact::class : null,
                'message_type' => \App\Models\Message::TYPE_INCOMING,
                'content' => $this->email['body'] ?? null,
                'content_type' => \App\Models\Message::CONTENT_TEXT,
                'external_source_id' => $messageId,
            ]);

            try {
                event(new \App\Events\Message\MessageCreated($msg));
            } catch (\Throwable $e) {
                Log::warning('ProcessInboundEmailJob: MessageCreated event failed', ['error' => $e->getMessage()]);
            }

            // Handle attachments if present
            $attachments = $this->email['attachments'] ?? null;
            if ($attachments && is_array($attachments)) {
                $stored = 0;
                foreach ($attachments as $att) {
                    try {
                        $fileName = null;
                        $content = null;
                        $contentType = null;

                        // Case A: attachment provided as URL string
                        if (is_string($att) && filter_var($att, FILTER_VALIDATE_URL)) {
                            $resp = Http::get($att);
                            if (! $resp->successful()) {
                                Log::warning('ProcessInboundEmailJob: failed to download attachment', ['url' => $att, 'status' => $resp->status()]);
                                continue;
                            }
                            $content = $resp->body();
                            $contentType = $resp->header('Content-Type') ?? null;
                            $fileName = pathinfo(parse_url($att, PHP_URL_PATH) ?: 'file', PATHINFO_BASENAME) ?: 'attachment';
                        }

                        // Case B: attachment provided as associative array with base64 content
                        if (is_array($att) && (! empty($att['content']) || ! empty($att['base64']))) {
                            $b64 = $att['content'] ?? $att['base64'];
                            $content = base64_decode($b64);
                            $contentType = $att['content_type'] ?? $att['mime'] ?? null;
                            $fileName = $att['filename'] ?? $att['name'] ?? 'attachment';
                        }

                        // Case C: provided as uploaded file info (path) - best-effort (not always available in queued job)
                        if (is_array($att) && ! empty($att['tmp_path']) && file_exists($att['tmp_path'])) {
                            $content = file_get_contents($att['tmp_path']);
                            $fileName = $att['filename'] ?? basename($att['tmp_path']);
                            $contentType = $att['content_type'] ?? mime_content_type($att['tmp_path']);
                        }

                        if (! $content) {
                            continue;
                        }

                        $ext = pathinfo($fileName, PATHINFO_EXTENSION) ?: null;
                        $safeName = Str::slug(pathinfo($fileName, PATHINFO_FILENAME));
                        $storagePath = "attachments/{$msg->account_id}/" . $safeName . '-' . time() . ($ext ? ".{$ext}" : '');

                        Storage::disk('public')->put($storagePath, $content);
                        $dataUrl = Storage::url($storagePath);
                        $size = strlen($content);

                        $attachment = \App\Models\Attachment::create([
                            'file_type' => $this->getFileType($contentType ?? 'application/octet-stream'),
                            'file_name' => $fileName,
                            'file_size' => $size,
                            'content_type' => $contentType,
                            'account_id' => $inbox->account_id,
                            'message_id' => $msg->id,
                            'data_url' => $dataUrl,
                            'extension' => $ext,
                            'meta' => is_array($att) ? $att : null,
                        ]);

                        $stored++;
                    } catch (\Throwable $e) {
                        Log::warning('ProcessInboundEmailJob: failed to store attachment', ['error' => $e->getMessage()]);
                    }
                }

                // Basic metric: increment Redis counter for stored attachments
                try {
                    if ($stored > 0) {
                        Redis::incrby('metrics:inbound_email_attachments', $stored);
                    }
                } catch (\Throwable $_) {
                    // ignore metrics failures
                }
            }

        } catch (\Throwable $e) {
            Log::error('ProcessInboundEmailJob failed', ['error' => $e->getMessage(), 'email' => $this->email]);
            throw $e;
        }
    }
}
