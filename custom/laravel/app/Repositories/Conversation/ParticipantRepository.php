<?php

namespace App\Repositories\Conversation;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class ParticipantRepository extends BaseRepository
{
    public function __construct(ConversationParticipant $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all participants for a conversation
     */
    public function getParticipants(Conversation $conversation): Collection
    {
        return $conversation->conversationParticipants()->with('user')->get();
    }

    /**
     * Get current participant IDs for a conversation
     */
    public function getCurrentParticipantIds(Conversation $conversation): array
    {
        return $conversation->conversationParticipants()->pluck('user_id')->toArray();
    }

    /**
     * Find or create a participant
     */
    public function findOrCreateParticipant(Conversation $conversation, int $userId): ConversationParticipant
    {
        return ConversationParticipant::firstOrCreate([
            'conversation_id' => $conversation->id,
            'user_id' => $userId,
        ], [
            'account_id' => $conversation->account_id,
        ]);
    }

    /**
     * Create a new participant
     */
    public function createParticipant(Conversation $conversation, int $userId): ConversationParticipant
    {
        return ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $userId,
            'account_id' => $conversation->account_id,
        ]);
    }

    /**
     * Remove participants from a conversation
     */
    public function removeParticipants(Conversation $conversation, array $userIds): void
    {
        $conversation->conversationParticipants()
            ->whereIn('user_id', $userIds)
            ->delete();
    }

    /**
     * Check if user is already a participant
     */
    public function isParticipant(Conversation $conversation, int $userId): bool
    {
        return $conversation->conversationParticipants()
            ->where('user_id', $userId)
            ->exists();
    }
}