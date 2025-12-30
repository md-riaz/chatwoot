<?php

namespace App\Actions\Conversation;

use App\Models\Conversation;
use App\Models\Label;
use Lorisleiva\Actions\Concerns\AsAction;

class AddLabelsToConversationAction
{
    use AsAction;

    /**
     * Add labels to a conversation. Labels can be provided as titles (string)
     * or existing label IDs.
     */
    public function handle(Conversation $conversation, array $labels): Conversation
    {
        $accountId = $conversation->account_id;

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

            $lbl = Label::firstOrCreate([
                'account_id' => $accountId,
                'title' => $title,
            ], [
                'title' => $title,
            ]);

            $labelIds[] = $lbl->id;
        }

        if (! empty($labelIds)) {
            $conversation->labels()->syncWithoutDetaching($labelIds);
        }

        return $conversation->fresh();
    }
}
