<?php

namespace App\Actions\Conversation;

use App\Models\Conversation;
use Lorisleiva\Actions\Concerns\AsAction;

class ChangePriorityAction
{
    use AsAction;

    public function handle(Conversation $conversation, $priority): Conversation
    {
        // Treat 'nil' (string) as explicit null
        if ($priority === 'nil' || $priority === 'null' || $priority === '' || $priority === null) {
            $conversation->update(['priority' => null]);
        } else {
            $conversation->update(['priority' => $priority]);
        }

        return $conversation->fresh();
    }
}
