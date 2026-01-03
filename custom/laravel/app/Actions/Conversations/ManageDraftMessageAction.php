<?php

namespace App\Actions\Conversations;

use App\Models\Conversation;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

class ManageDraftMessageAction
{
    use AsAction;

    /**
     * Get draft message for a conversation and user.
     */
    public function getDraft(Conversation $conversation, int $userId): ?array
    {
        $cacheKey = $this->getDraftCacheKey($conversation, $userId);
        return Cache::get($cacheKey);
    }

    /**
     * Save draft message for a conversation and user.
     */
    public function saveDraft(
        Conversation $conversation,
        int $userId,
        string $message,
        ?string $clientUpdatedAt = null
    ): array {
        $cacheKey = $this->getDraftCacheKey($conversation, $userId);
        
        // Check for conflicts if client provides updated_at timestamp
        if ($clientUpdatedAt) {
            $existingDraft = Cache::get($cacheKey);
            if ($existingDraft && $existingDraft['updated_at'] > $clientUpdatedAt) {
                throw new \Illuminate\Validation\ValidationException(
                    validator([], [])->errors()->add(
                        'draft_message',
                        'Draft has been updated by another session. Please refresh and try again.'
                    )
                );
            }
        }

        $draftData = [
            'message' => $message,
            'updated_at' => now()->toISOString(),
            'user_id' => $userId,
        ];
        
        // Store draft for 7 days
        Cache::put($cacheKey, $draftData, now()->addDays(7));

        return $draftData;
    }

    /**
     * Delete draft message for a conversation and user.
     */
    public function deleteDraft(Conversation $conversation, int $userId): void
    {
        $cacheKey = $this->getDraftCacheKey($conversation, $userId);
        Cache::forget($cacheKey);
    }

    /**
     * Get the cache key for the draft message (user-specific).
     */
    private function getDraftCacheKey(Conversation $conversation, int $userId): string
    {
        return "conversation_draft_message:{$conversation->id}:user:{$userId}";
    }

    /**
     * Clean up expired drafts (can be called by a scheduled job).
     */
    public function cleanupExpiredDrafts(): void
    {
        // This would require a more sophisticated cache implementation
        // or storing draft keys in a separate index for cleanup
        // For now, we rely on cache TTL for automatic cleanup
    }
}