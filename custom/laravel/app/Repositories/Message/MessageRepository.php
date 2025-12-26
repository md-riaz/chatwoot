<?php

namespace App\Repositories\Message;

use App\Models\Message;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class MessageRepository extends BaseRepository
{
    public function __construct(Message $model)
    {
        parent::__construct($model);
    }

    /**
     * Get messages for a conversation.
     */
    public function getForConversation(int $conversationId, int $perPage = 50): LengthAwarePaginator
    {
        return $this->model
            ->where('conversation_id', $conversationId)
            ->with(['sender', 'attachments'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get public messages for a conversation.
     */
    public function getPublicForConversation(int $conversationId): Collection
    {
        return $this->model
            ->where('conversation_id', $conversationId)
            ->public()
            ->with(['sender', 'attachments'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get the latest message for a conversation.
     */
    public function getLatestForConversation(int $conversationId): ?Message
    {
        return $this->model
            ->where('conversation_id', $conversationId)
            ->public()
            ->latest()
            ->first();
    }

    /**
     * Count messages by type for a conversation.
     */
    public function countByTypeForConversation(int $conversationId): array
    {
        return $this->model
            ->where('conversation_id', $conversationId)
            ->selectRaw('message_type, count(*) as count')
            ->groupBy('message_type')
            ->pluck('count', 'message_type')
            ->toArray();
    }
}
