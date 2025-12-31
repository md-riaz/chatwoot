<?php

namespace App\Jobs\Channels;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;
use App\Data\Channels\InboundMessageData;
use App\Models\Inbox;
use App\Services\Channels\InboundMessageService;

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
            $raw = $this->email;
            $messageId = $raw['message_id'] ?? null;
            $to = $raw['to'] ?? null;

            if (! $to) {
                Log::warning('ProcessInboundEmailJob: missing recipient', ['email' => $this->email]);
                return;
            }

            // Resolve inbox by recipient address (simple heuristic)
            $inbox = Inbox::whereHasMorph('channel', [\App\Models\Channels\Email::class], function ($q) use ($to) {
                $q->where('email', strtolower($to))
                    ->orWhere('forward_to_email', strtolower($to));
            })->first();

            if (! $inbox) {
                Log::warning('ProcessInboundEmailJob: no inbox for recipient', ['to' => $to]);
                return;
            }

            // Idempotency: skip if message with same external_source_id exists
            if ($messageId && \App\Models\Message::where('external_source_id', $messageId)->exists()) {
                Log::info('ProcessInboundEmailJob: duplicate message, skipping', ['message_id' => $messageId]);
                return;
            }

            $contactIdentifier = $this->email['from'] ? 'email:' . strtolower($this->email['from']) : 'email:unknown';

            $service = app(InboundMessageService::class);
            $messageData = new InboundMessageData(
                account_id: $inbox->account_id,
                inbox_id: $inbox->id,
                contact_identifier: $contactIdentifier,
                contact_source: 'email',
                contact_name: $this->email['from_name'] ?? null,
                contact_email: $this->email['from'] ?? null,
                contact_phone: null,
                provider_contact_id: $this->email['from'] ?? null,
                content: $this->email['body'] ?? null,
                content_type: \App\Models\Message::CONTENT_TEXT,
                external_source_id: $messageId,
                attachments: is_array($this->email['attachments'] ?? null) ? $this->email['attachments'] : [],
                metadata: [
                    'subject' => $this->email['subject'] ?? null,
                    'headers' => $raw['headers'] ?? ($raw['message'] ?? null),
                ]
            );

            $msg = $service->ingest($messageData);

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
