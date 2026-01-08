<?php

namespace App\Actions\Conversation;

use App\Models\Conversation;
use App\Models\Label;
use Lorisleiva\Actions\Concerns\AsAction;

class RemoveLabelsFromConversationAction
{
    use AsAction;

    /**
     * Remove labels from a conversation. Accepts label ids or titles.
     */
    public function handle(Conversation $conversation, array $labels): Conversation
    {
        $labelIds = [];

        foreach ($labels as $label) {
            if (is_int($label) || ctype_digit((string) $label)) {
                $labelIds[] = (int) $label;
                continue;
            }

            $title = trim((string) $label);
            if ($title === '') {
                continue;
            }

            $lbl = Label::where('account_id', $conversation->account_id)
                ->where('title', $title)
                ->first();

            if ($lbl) {
                $labelIds[] = $lbl->id;
            }
        }

        if (! empty($labelIds)) {
            $conversation->labels()->detach($labelIds);
        }

        return $conversation->fresh();
    }
}
