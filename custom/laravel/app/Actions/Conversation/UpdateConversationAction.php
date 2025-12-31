<?php

namespace App\Actions\Conversation;

use App\Events\Conversation\ConversationStatusChanged;
use App\Events\Conversation\ConversationUpdated;
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
        $original = $conversation->getOriginal();

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

        $conversation->refresh();

        $changes = $this->detectChanges($conversation, $original, $data);

        if (array_key_exists('status', $changes)) {
            event(new ConversationStatusChanged(
                $conversation,
                $changes['status']['previous'],
                $changes['status']['current']
            ));
        }

        if (! empty($changes)) {
            event(new ConversationUpdated($conversation, $changes));
        }

        return $conversation;
    }

    private function detectChanges(Conversation $conversation, array $original, array $payload): array
    {
        $tracked = ['status', 'priority', 'team_id', 'assignee_id', 'snoozed_until', 'custom_attributes'];
        $changes = [];

        foreach ($tracked as $field) {
            if (! array_key_exists($field, $payload)) {
                continue;
            }

            $previous = $original[$field] ?? null;
            $current = $conversation->{$field};

            if ($previous != $current) {
                $changes[$field] = [
                    'previous' => $previous,
                    'current' => $current,
                ];
            }
        }

        return $changes;
    }
}
