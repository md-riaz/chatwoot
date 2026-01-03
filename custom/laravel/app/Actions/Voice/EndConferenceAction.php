<?php

namespace App\Actions\Voice;

use App\Models\Account;
use App\Models\Conversation;
use App\Services\Voice\Provider\Twilio\ConferenceService;
use Lorisleiva\Actions\Concerns\AsAction;

class EndConferenceAction
{
    use AsAction;

    public function __construct(
        private ConferenceService $conferenceService
    ) {}

    /**
     * End a conference call.
     */
    public function handle(Account $account, string $conversationDisplayId): array
    {
        $conversation = $account->conversations()
            ->where('display_id', $conversationDisplayId)
            ->firstOrFail();

        $this->conferenceService->endConference($conversation);

        return [
            'status' => 'success',
            'id' => $conversation->display_id,
        ];
    }
}