<?php

namespace App\Actions\Csat;

use App\Jobs\Notification\SendCsatSurveyJob;
use App\Models\Conversation;
use Lorisleiva\Actions\Concerns\AsAction;

class DispatchCsatSurveyAction
{
    use AsAction;

    public function handle(Conversation $conversation): void
    {
        if ($conversation->status !== Conversation::STATUS_RESOLVED) {
            return;
        }

        $inbox = $conversation->inbox;
        if (! $inbox || ! $inbox->csat_survey_enabled) {
            return;
        }

        // Only send once if a CSAT response already exists
        if ($conversation->csatSurveyResponse()->exists()) {
            return;
        }

        $contact = $conversation->contact;
        if (! $contact || ! $contact->email) {
            return;
        }

        SendCsatSurveyJob::dispatch($conversation->id)->onQueue('notifications');
    }
}
