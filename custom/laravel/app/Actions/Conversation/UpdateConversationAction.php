<?php

namespace App\Actions\Conversation;

use App\Models\Conversation;
use App\Repositories\Conversation\ConversationRepository;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateConversationAction
{
    use AsAction;

    public function __construct(
        private ConversationRepository $conversationRepository
    ) {}

    public function handle(Conversation $conversation, array $data): Conversation
    {
        // Normalize some legacy sentinel values to null like Rails behavior
        if (array_key_exists('priority', $data)) {
            if ($data['priority'] === 'nil' || $data['priority'] === 'null' || $data['priority'] === '') {
                $data['priority'] = null;
            }
        }

        if (array_key_exists('team_id', $data)) {
            if ($data['team_id'] === 'nil' || $data['team_id'] === '0' || $data['team_id'] === '') {
                $data['team_id'] = null;
            }
        }

        if (array_key_exists('assignee_id', $data)) {
            if ($data['assignee_id'] === 'nil' || $data['assignee_id'] === '') {
                $data['assignee_id'] = null;
            }
        }

        $this->conversationRepository->update($conversation->id, $data);

        // Trigger event
        // event(new ConversationUpdated($conversation));

        return $conversation->fresh();
    }
}
