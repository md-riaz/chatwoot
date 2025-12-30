<?php

namespace App\Actions\Conversation;

use App\Mail\ConversationTranscriptMailable;
use App\Models\Conversation;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class SendTranscriptAction
{
    use AsAction;

    public function handle(Conversation $conversation, array $emails): void
    {
        foreach ($emails as $email) {
            $emailClean = trim($email);
            if (! filter_var($emailClean, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            Mail::to($emailClean)->queue(new ConversationTranscriptMailable($conversation));
        }
    }
}
