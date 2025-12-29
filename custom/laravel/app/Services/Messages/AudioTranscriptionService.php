<?php

namespace App\Services\Messages;

use App\Models\Attachment;
use App\Models\Media;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Services\Integrations\OpenAIService;

class AudioTranscriptionService
{
    protected $attachment;
    protected $message;
    protected $account;

    const WHISPER_MODEL = 'whisper-1';

    public function __construct(Attachment $attachment)
    {
        $this->attachment = $attachment;
        $this->message = $attachment->message;
        $this->account = $this->message->account;
    }

    public function perform()
    {
        if (!$this->canTranscribe()) {
            return ['error' => 'Transcription limit exceeded or feature not enabled'];
        }
        if (!$this->message) {
            return ['error' => 'Message not found'];
        }
        $transcriptions = $this->transcribeAudio();
        Log::info('Audio transcription successful', ['transcriptions' => $transcriptions]);
        return ['success' => true, 'transcriptions' => $transcriptions];
    }

    protected function canTranscribe(): bool
    {
        if (!$this->account) return false;
        if (!method_exists($this->account, 'featureEnabled') || !$this->account->featureEnabled('captain_integration')) {
            return false;
        }

        // If account exposes usage limits, respect them; otherwise allow
        if (method_exists($this->account, 'usageLimits')) {
            $limits = $this->account->usageLimits();
            $available = $limits['captain']['responses']['current_available'] ?? null;
            if ($available !== null) {
                return (int) $available > 0;
            }
        }

        return true;
    }

    protected function fetchAudioFile(): string
    {
        $localPath = storage_path('app/tmp/audio-transcriptions');
        if (!is_dir($localPath)) {
            mkdir($localPath, 0777, true);
        }

        // Try to find associated Media record for this attachment
        $media = $this->attachment->media()->where('file_type', Media::TYPE_AUDIO)->orderBy('id')->first();

        $fileName = $media->file_name ?? ($this->attachment->file_name ?? uniqid('audio_'));
        $filePath = $localPath . '/' . $fileName;

        if ($media) {
            // External URL (CDN or remote)
            if (!empty($media->external_url)) {
                $resp = Http::timeout(60)->get($media->external_url);
                if (!$resp->successful()) {
                    throw new \RuntimeException('Failed to download external media');
                }
                file_put_contents($filePath, $resp->body());
                return $filePath;
            }

            // Stored in configured disk
            if (!empty($media->file_path)) {
                $disk = $media->disk ?? config('filesystems.default');
                $content = Storage::disk($disk)->get($media->file_path);
                if ($content === false || $content === null) {
                    throw new \RuntimeException('Media file not available on storage');
                }
                file_put_contents($filePath, $content);
                return $filePath;
            }
        }

        // Fallback: attempt to use attachment external_url or stored external_url
        if (!empty($this->attachment->external_url)) {
            $resp = Http::timeout(60)->get($this->attachment->external_url);
            if (!$resp->successful()) {
                throw new \RuntimeException('Failed to download attachment external_url');
            }
            file_put_contents($filePath, $resp->body());
            return $filePath;
        }

        throw new \RuntimeException('No media available to transcribe');
    }

    protected function transcribeAudio()
    {
        $meta = $this->attachment->meta ?? [];
        if (!empty($meta['transcribed_text'])) {
            return $meta['transcribed_text'];
        }
        $filePath = $this->fetchAudioFile();
        $openai = new OpenAIService();
        $response = $openai->transcribeAudio($filePath, self::WHISPER_MODEL);
        $transcribedText = $response['text'] ?? ($response['content'] ?? '');
        if (!empty($transcribedText)) {
            $this->updateTranscription($transcribedText);

            // increment account usage if supported
            try {
                if (method_exists($this->message->account, 'incrementResponseUsage')) {
                    $this->message->account->incrementResponseUsage();
                }
            } catch (\Exception $e) {
                Log::warning('Failed to increment account response usage', ['error' => $e->getMessage()]);
            }
        }

        @unlink($filePath);
        return $transcribedText;
    }

    protected function updateTranscription($transcribedText)
    {
        if (empty($transcribedText)) return;
        $meta = $this->attachment->meta ?? [];
        $meta['transcribed_text'] = $transcribedText;
        $this->attachment->meta = $meta;
        $this->attachment->save();
        $this->message->refresh();
        // Broadcast message updated so UI and consumers receive the new transcription
        try {
            event(new \App\Events\Message\MessageUpdated($this->message));
        } catch (\Exception $e) {
            Log::warning('Failed to dispatch MessageUpdated event', ['error' => $e->getMessage()]);
        }
        // Trigger reindex if the message supports it
        try {
            if (method_exists($this->message, 'reindex')) {
                $this->message->reindex();
            }
        } catch (\Exception $e) {
            Log::warning('Failed to reindex message', ['error' => $e->getMessage()]);
        }
    }
}
