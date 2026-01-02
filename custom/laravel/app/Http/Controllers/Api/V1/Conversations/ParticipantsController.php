<?php

namespace App\Http\Controllers\Api\V1\Conversations;

use App\Actions\Conversation\ManageParticipantsAction;
use App\Data\Conversation\ParticipantData;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Models\Account;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class ParticipantsController extends Controller
{
    /**
     * Display the participants for a conversation.
     * Equivalent to Rails ParticipantsController#show
     */
    public function show(Account $account, Conversation $conversation): AnonymousResourceCollection
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $participants = ManageParticipantsAction::run()->getParticipants($conversation);
        
        // Return users like Rails does (through the participant relationship)
        $users = $participants->map(fn($participant) => $participant->user);

        return UserResource::collection($users);
    }

    /**
     * Add participants to a conversation.
     * Equivalent to Rails ParticipantsController#create
     */
    public function create(ParticipantData $data, Account $account, Conversation $conversation): AnonymousResourceCollection
    {
        abort_unless($conversation->account_id === $account->id, 404);

        try {
            $participants = ManageParticipantsAction::run()->addParticipants($conversation, $data->user_ids);

            // Return users like Rails does
            $users = $participants->map(fn($participant) => $participant->user);

            return UserResource::collection($users);
        } catch (ValidationException $e) {
            throw $e;
        }
    }

    /**
     * Update participants for a conversation (replace existing).
     * Equivalent to Rails ParticipantsController#update
     */
    public function update(ParticipantData $data, Account $account, Conversation $conversation): AnonymousResourceCollection
    {
        abort_unless($conversation->account_id === $account->id, 404);

        try {
            $participants = ManageParticipantsAction::run()->updateParticipants($conversation, $data->user_ids);

            // Return updated participants like Rails does
            $users = $participants->map(fn($participant) => $participant->user);

            return UserResource::collection($users);
        } catch (ValidationException $e) {
            throw $e;
        }
    }

    /**
     * Remove participants from a conversation.
     * Equivalent to Rails ParticipantsController#destroy
     */
    public function destroy(ParticipantData $data, Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        ManageParticipantsAction::run()->removeParticipants($conversation, $data->user_ids);

        return response()->json(null, 200); // Rails returns 200 OK, not 204
    }
}
