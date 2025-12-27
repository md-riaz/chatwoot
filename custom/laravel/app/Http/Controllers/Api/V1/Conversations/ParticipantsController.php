<?php

namespace App\Http\Controllers\Api\V1\Conversations;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class ParticipantsController extends Controller
{
    /**
     * Display the participants for a conversation.
     */
    public function show(Account $account, Conversation $conversation): JsonResource
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $participants = $conversation->participants()->with('user')->get();

        return JsonResource::collection($participants);
    }

    /**
     * Add participants to a conversation.
     */
    public function store(Request $request, Account $account, Conversation $conversation): JsonResource
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $participants = [];

        DB::transaction(function () use ($validated, $conversation, &$participants) {
            foreach ($validated['user_ids'] as $userId) {
                $participants[] = ConversationParticipant::firstOrCreate([
                    'conversation_id' => $conversation->id,
                    'user_id' => $userId,
                    'account_id' => $conversation->account_id,
                ]);
            }
        });

        return JsonResource::collection(collect($participants));
    }

    /**
     * Update participants for a conversation (replace existing).
     */
    public function update(Request $request, Account $account, Conversation $conversation): JsonResource
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        DB::transaction(function () use ($validated, $conversation) {
            $currentParticipantIds = $conversation->participants()->pluck('user_id')->toArray();
            $newParticipantIds = $validated['user_ids'];

            // Add new participants
            $toAdd = array_diff($newParticipantIds, $currentParticipantIds);
            foreach ($toAdd as $userId) {
                ConversationParticipant::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $userId,
                    'account_id' => $conversation->account_id,
                ]);
            }

            // Remove participants no longer in the list
            $toRemove = array_diff($currentParticipantIds, $newParticipantIds);
            if (! empty($toRemove)) {
                $conversation->participants()->whereIn('user_id', $toRemove)->delete();
            }
        });

        $participants = $conversation->participants()->with('user')->get();

        return JsonResource::collection($participants);
    }

    /**
     * Remove participants from a conversation.
     */
    public function destroy(Request $request, Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        DB::transaction(function () use ($validated, $conversation) {
            $conversation->participants()->whereIn('user_id', $validated['user_ids'])->delete();
        });

        return response()->json(null, 204);
    }
}
