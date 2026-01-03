<?php

namespace App\Actions\Voice;

use App\Models\Account;
use App\Models\Conversation;
use App\Models\User;
use App\Services\Voice\Provider\Twilio\ConferenceService;
use Lorisleiva\Actions\Concerns\AsAction;

class JoinConferenceAction
{
    use AsAction;

    public function __construct(
        private ConferenceService $conferenceService
    ) {}

    /**
     * Join an agent to a conference call.
     */
    public function handle(Account $account, User $user, string $conversationDisplayId, ?string $callSid = null): array
    {
        $conversation = $account->conversations()
            ->where('display_id', $conversationDisplayId)
            ->firstOrFail();

        $this->ensureCallSid($conversation, $callSid);

        $conferenceSid = $this->conferenceService->ensureConferenceSid($conversation);
        $this->conferenceService->markAgentJoined($conversation, $user);

        return [
            'status' => 'success',
            'id' => $conversation->display_id,
            'conference_sid' => $conferenceSid,
            'using_webrtc' => true,
        ];
    }

    private function ensureCallSid(Conversation $conversation, ?string $callSid): void
    {
        if ($conversation->identifier) {
            return;
        }

        if (!$callSid) {
            throw new \InvalidArgumentException('call_sid required when conversation has no identifier');
        }

        $conversation->update(['identifier' => $callSid]);
    }
}