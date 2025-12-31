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

    public string $queue = 'attachments';
    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Backoff seconds between attempts.
     */
    public $backoff = [2, 10];

    public int $timeout = 300;

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

        // Run the transcription service. Let exceptions bubble so the job can be retried.
        $service = new AudioTranscriptionService($attachment);
        $service->perform();
    }
}
