<?php

namespace App\Jobs\Notification;

use App\Jobs\Webhooks\SendWebhooksJob;
use App\Models\Conversation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCsatSurveyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 300;

    public int $timeout = 180;

    public string $queue = 'notifications';

    public function __construct(public int $conversationId) {}

    public function handle(): void
    {
        $conversation = Conversation::with(['contact', 'inbox'])
            ->find($this->conversationId);

        if (! $conversation || $conversation->status !== Conversation::STATUS_RESOLVED) {
            return;
        }

        $contact = $conversation->contact;
        $inbox = $conversation->inbox;

        if (! $contact || ! $contact->email || ! $inbox?->csat_survey_enabled) {
            return;
        }

        if ($conversation->csatSurveyResponse()->exists()) {
            return;
        }

        $surveyUrl = url("/api/v1/public/csat/{$conversation->uuid}");
        $subject = sprintf('%s: We value your feedback', config('app.name', 'ClearLine'));
        $name = trim($contact->name ?: '') ?: 'there';
        $body = "Hi {$name},\n\nWe recently resolved your conversation and would love to hear your feedback. Please rate your experience here: {$surveyUrl}\n\nThank you!";

        Mail::raw($body, function ($message) use ($contact, $subject) {
            $message->to($contact->email)
                ->subject($subject);
        });

        SendWebhooksJob::dispatch($conversation->account_id, 'csat_survey_dispatched', [
            'conversation_id' => $conversation->id,
            'contact_id' => $contact->id,
            'survey_url' => $surveyUrl,
        ])->onQueue('webhooks');

        Log::info('CSAT survey dispatched', [
            'conversation_id' => $conversation->id,
            'contact_id' => $contact->id,
        ]);
    }
}
