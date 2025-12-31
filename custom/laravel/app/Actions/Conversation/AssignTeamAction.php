<?php

namespace App\Actions\Conversation;

use App\Events\Conversation\ConversationUpdated;
use App\Models\Conversation;
use App\Models\Team;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Support\Facades\Log;

class AssignTeamAction
{
    use AsAction;

    public function handle(Conversation $conversation, $teamId): Conversation
    {
        $previousTeam = $conversation->team_id;

        // normalize unassign sentinel values
        if (is_null($teamId) || $teamId === '' || $teamId === 'nil' || $teamId === 0 || $teamId === '0') {
            if (is_null($previousTeam)) {
                return $conversation;
            }

            $conversation->update(['team_id' => null]);
            $conversation->refresh();

            event(new ConversationUpdated($conversation, [
                'team_id' => [
                    'previous' => $previousTeam,
                    'current' => null,
                ],
            ]));

            return $conversation;
        }

        $team = Team::where('account_id', $conversation->account_id)->find($teamId);

        if (! $team) {
            Log::warning('AssignTeamAction: team not found or does not belong to account', ['team_id' => $teamId, 'account_id' => $conversation->account_id]);
            $conversation->refresh();
            return $conversation;
        }

        $conversation->update(['team_id' => $team->id]);

        $conversation->refresh();

        if ($previousTeam != $conversation->team_id) {
            event(new ConversationUpdated($conversation, [
                'team_id' => [
                    'previous' => $previousTeam,
                    'current' => $conversation->team_id,
                ],
            ]));
        }

        return $conversation;
    }
}
