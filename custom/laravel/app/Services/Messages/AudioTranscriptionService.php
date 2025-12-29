<?php

namespace App\Services\Messages;

use App\Models\Attachment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
        // Example: check feature flag and usage limits (implement as needed)
        if (!$this->account->feature_enabled('captain_integration')) return false;
        if (empty($this->account->audio_transcriptions)) return false;
        // Usage limit check (stub)
        return true;
    }

    protected function fetchAudioFile(): string
    {
        $localPath = storage_path('app/tmp/audio-transcriptions');
        if (!is_dir($localPath)) {
            mkdir($localPath, 0777, true);
        }
        $fileName = $this->attachment->file_name ?? uniqid('audio_');
        $filePath = $localPath . '/' . $fileName;
        Storage::disk('local')->put($filePath, $this->attachment->getRawFile());
        return $filePath;
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
        $transcribedText = $response['text'] ?? '';
        $this->updateTranscription($transcribedText);
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
        // Optionally: trigger update event, increment usage, reindex, etc.
    }
}
