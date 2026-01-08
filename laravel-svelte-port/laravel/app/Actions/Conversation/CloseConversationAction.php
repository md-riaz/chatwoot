<?php

namespace App\Actions\Conversation;

use App\Events\Conversation\ConversationStatusChanged;
use App\Events\Conversation\ConversationUpdated;
use App\Models\Conversation;
use App\Repositories\Conversation\ConversationRepository;
use Lorisleiva\Actions\Concerns\AsAction;

class CloseConversationAction
{
    use AsAction;

    public function __construct(
        private ConversationRepository $conversationRepository
    ) {}

    public function handle(Conversation $conversation): Conversation
    {
        $previousStatus = $conversation->status;

        $this->conversationRepository->update($conversation->id, [
            'status' => Conversation::STATUS_RESOLVED,
        ]);

        $conversation = $conversation->fresh();

        if ($previousStatus !== Conversation::STATUS_RESOLVED) {
            event(new ConversationStatusChanged($conversation, $previousStatus, Conversation::STATUS_RESOLVED));
            event(new ConversationUpdated($conversation, [
                'status' => [
                    'previous' => $previousStatus,
                    'current' => Conversation::STATUS_RESOLVED,
                ],
            ]));
        }

        return $conversation;
    }
}
