<?php

namespace App\Jobs\Message;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Attachment;
use App\Services\Messages\AudioTranscriptionService;

class AudioTranscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $attachmentId;

    public string $queue = 'low';

    public function __construct(int $attachmentId)
    {
        $this->attachmentId = $attachmentId;
    }

    public function handle(): void
    {
        $attachment = Attachment::find($this->attachmentId);
        if (!$attachment) {
            return;
        }
        if (!$attachment->isAudio()) {
            return;
        }

        // Run the transcription service
        $service = new AudioTranscriptionService($attachment);
        $service->perform();
    }
}
