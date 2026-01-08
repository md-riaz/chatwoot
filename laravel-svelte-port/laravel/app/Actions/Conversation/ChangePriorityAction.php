<?php

namespace App\Actions\Conversation;

use App\Events\Conversation\ConversationUpdated;
use App\Models\Conversation;
use Lorisleiva\Actions\Concerns\AsAction;

class ChangePriorityAction
{
    use AsAction;

    public function handle(Conversation $conversation, $priority): Conversation
    {
        $previousPriority = $conversation->priority;

        // Treat 'nil' (string) as explicit null
        if ($priority === 'nil' || $priority === 'null' || $priority === '' || $priority === null) {
            $conversation->update(['priority' => null]);
        } else {
            $conversation->update(['priority' => $priority]);
        }

        $conversation = $conversation->fresh();

        if ($previousPriority != $conversation->priority) {
            event(new ConversationUpdated($conversation, [
                'priority' => [
                    'previous' => $previousPriority,
                    'current' => $conversation->priority,
                ],
            ]));
        }

        return $conversation;
    }
}
