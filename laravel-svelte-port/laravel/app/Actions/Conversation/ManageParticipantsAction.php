<?php

namespace App\Actions\Conversation;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use App\Repositories\Conversation\ParticipantRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class ManageParticipantsAction
{
    use AsAction;

    private ParticipantRepository $participantRepository;

    public function __construct()
    {
        $this->participantRepository = new ParticipantRepository(new ConversationParticipant());
    }

    /**
     * Get all participants for a conversation
     */
    public function getParticipants(Conversation $conversation): Collection
    {
        return $this->participantRepository->getParticipants($conversation);
    }

    /**
     * Add participants to a conversation
     */
    public function addParticipants(Conversation $conversation, array $userIds): Collection
    {
        $this->validateParticipants($conversation, $userIds);

        return DB::transaction(function () use ($conversation, $userIds) {
            $participants = [];
            
            foreach ($userIds as $userId) {
                $participants[] = $this->participantRepository->findOrCreateParticipant(
                    $conversation,
                    $userId
                );
            }

            return collect($participants);
        });
    }

    /**
     * Update participants for a conversation (replace existing)
     */
    public function updateParticipants(Conversation $conversation, array $userIds): Collection
    {
        $this->validateParticipants($conversation, $userIds);

        return DB::transaction(function () use ($conversation, $userIds) {
            $currentParticipantIds = $this->participantRepository->getCurrentParticipantIds($conversation);

            // Add new participants
            $toAdd = array_diff($userIds, $currentParticipantIds);
            foreach ($toAdd as $userId) {
                $this->participantRepository->createParticipant($conversation, $userId);
            }

            // Remove participants no longer in the list
            $toRemove = array_diff($currentParticipantIds, $userIds);
            if (! empty($toRemove)) {
                $this->participantRepository->removeParticipants($conversation, $toRemove);
            }

            return $this->participantRepository->getParticipants($conversation);
        });
    }

    /**
     * Remove participants from a conversation
     */
    public function removeParticipants(Conversation $conversation, array $userIds): void
    {
        DB::transaction(function () use ($conversation, $userIds) {
            $this->participantRepository->removeParticipants($conversation, $userIds);
        });
    }

    /**
     * Validate that all users can participate in the conversation
     */
    private function validateParticipants(Conversation $conversation, array $userIds): void
    {
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user && ! $this->canUserParticipate($conversation, $user)) {
                throw ValidationException::withMessages([
                    'user_ids' => ["User {$userId} must have inbox access"],
                ]);
            }
        }
    }

    /**
     * Check if a user can be added as a participant
     */
    private function canUserParticipate(Conversation $conversation, User $user): bool
    {
        return $conversation->inbox
            ->account
            ->users()
            ->where('users.id', $user->id)
            ->exists();
    }
}