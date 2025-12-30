<?php

namespace App\Actions\Conversation;

use App\Models\Conversation;
use App\Models\Team;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Support\Facades\Log;

class AssignTeamAction
{
    use AsAction;

    public function handle(Conversation $conversation, $teamId): Conversation
    {
        // normalize unassign sentinel values
        if (is_null($teamId) || $teamId === '' || $teamId === 'nil' || $teamId === 0 || $teamId === '0') {
            $conversation->update(['team_id' => null]);
            return $conversation->fresh();
        }

        $team = Team::where('account_id', $conversation->account_id)->find($teamId);

        if (! $team) {
            Log::warning('AssignTeamAction: team not found or does not belong to account', ['team_id' => $teamId, 'account_id' => $conversation->account_id]);
            return $conversation->fresh();
        }

        $conversation->update(['team_id' => $team->id]);

        return $conversation->fresh();
    }
}
