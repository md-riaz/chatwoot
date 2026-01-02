<?php

namespace App\Http\Controllers\Api\V1\Conversations;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Models\Account;
use App\Models\Conversation;
use App\Services\ParticipantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class ParticipantsController extends Controller
{
    public function __construct(
        private ParticipantService $participantService
    ) {}

    /**
     * Display the participants for a conversation.
     * Equivalent to Rails ParticipantsController#show
     */
    public function show(Account $account, Conversation $conversation): AnonymousResourceCollection
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $participants = $this->participantService->getParticipants($conversation);
        
        // Return users like Rails does (through the participant relationship)
        $users = $participants->map(fn($participant) => $participant->user);

        return UserResource::collection($users);
    }

    /**
     * Add participants to a conversation.
     * Equivalent to Rails ParticipantsController#create
     */
    public function create(Request $request, Account $account, Conversation $conversation): AnonymousResourceCollection
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        try {
            // Validate participants can access the inbox
            $this->participantService->validateParticipants($conversation, $validated['user_ids']);
            
            $participants = $this->participantService->addParticipants($conversation, $validated['user_ids']);

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
    public function update(Request $request, Account $account, Conversation $conversation): AnonymousResourceCollection
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        try {
            // Validate participants can access the inbox
            $this->participantService->validateParticipants($conversation, $validated['user_ids']);
            
            $participants = $this->participantService->updateParticipants($conversation, $validated['user_ids']);

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
    public function destroy(Request $request, Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $this->participantService->removeParticipants($conversation, $validated['user_ids']);

        return response()->json(null, 200); // Rails returns 200 OK, not 204
    }
}
