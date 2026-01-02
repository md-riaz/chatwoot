<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ParticipantService
{
    /**
     * Get all participants for a conversation
     */
    public function getParticipants(Conversation $conversation): Collection
    {
        return $conversation->conversationParticipants()->with('user')->get();
    }

    /**
     * Add participants to a conversation
     * Equivalent to Rails participants_to_be_added_ids logic
     */
    public function addParticipants(Conversation $conversation, array $userIds): Collection
    {
        $participants = [];

        DB::transaction(function () use ($conversation, $userIds, &$participants) {
            foreach ($userIds as $userId) {
                $participants[] = ConversationParticipant::firstOrCreate([
                    'conversation_id' => $conversation->id,
                    'user_id' => $userId,
                ], [
                    'account_id' => $conversation->account_id,
                ]);
            }
        });

        return collect($participants);
    }

    /**
     * Update participants for a conversation (replace existing)
     * Implements Rails participants_to_be_added_ids and participants_to_be_removed_ids logic
     */
    public function updateParticipants(Conversation $conversation, array $userIds): Collection
    {
        DB::transaction(function () use ($conversation, $userIds) {
            $currentParticipantIds = $this->getCurrentParticipantIds($conversation);

            // Add new participants (participants_to_be_added_ids)
            $toAdd = array_diff($userIds, $currentParticipantIds);
            foreach ($toAdd as $userId) {
                ConversationParticipant::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $userId,
                    'account_id' => $conversation->account_id,
                ]);
            }

            // Remove participants no longer in the list (participants_to_be_removed_ids)
            $toRemove = array_diff($currentParticipantIds, $userIds);
            if (! empty($toRemove)) {
                $conversation->conversationParticipants()->whereIn('user_id', $toRemove)->delete();
            }
        });

        return $this->getParticipants($conversation);
    }

    /**
     * Remove participants from a conversation
     */
    public function removeParticipants(Conversation $conversation, array $userIds): void
    {
        DB::transaction(function () use ($conversation, $userIds) {
            foreach ($userIds as $userId) {
                $conversation->conversationParticipants()
                    ->where('user_id', $userId)
                    ->first()
                    ?->delete();
            }
        });
    }

    /**
     * Get current participant IDs for a conversation
     * Equivalent to Rails current_participant_ids method
     */
    public function getCurrentParticipantIds(Conversation $conversation): array
    {
        return $conversation->conversationParticipants()->pluck('user_id')->toArray();
    }

    /**
     * Check if a user can be added as a participant
     * Validates inbox access like Rails ensure_inbox_access
     */
    public function canUserParticipate(Conversation $conversation, User $user): bool
    {
        return $conversation->inbox
            ->account
            ->users()
            ->where('users.id', $user->id)
            ->exists();
    }

    /**
     * Validate that all users can participate in the conversation
     */
    public function validateParticipants(Conversation $conversation, array $userIds): void
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
}