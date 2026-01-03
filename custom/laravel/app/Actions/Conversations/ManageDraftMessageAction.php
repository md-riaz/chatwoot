<?php

namespace App\Actions\Conversations;

use App\Models\Conversation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class ManageDraftMessageAction
{
    use AsAction;

    private const CACHE_TTL_DAYS = 7;
    private const CACHE_KEY_PREFIX = 'conversation_draft_message';

    /**
     * Get draft message for a conversation and user.
     */
    public function getDraft(Conversation $conversation, int $userId): ?array
    {
        $cacheKey = $this->getDraftCacheKey($conversation, $userId);
        
        try {
            return Cache::get($cacheKey);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve draft message', [
                'conversation_id' => $conversation->id,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
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
        
        // Trim and validate message
        $message = trim($message);
        if (empty($message)) {
            throw new \InvalidArgumentException('Draft message cannot be empty');
        }
        
        // Check for conflicts if client provides updated_at timestamp
        if ($clientUpdatedAt) {
            $existingDraft = Cache::get($cacheKey);
            if ($existingDraft && $existingDraft['updated_at'] > $clientUpdatedAt) {
                throw new \Illuminate\Validation\ValidationException(
                    validator([], [])->errors()->add(
                        'draft_message.updated_at',
                        'Draft has been updated by another session. Please refresh and try again.'
                    )
                );
            }
        }

        $draftData = [
            'message' => $message,
            'updated_at' => now()->toISOString(),
            'user_id' => $userId,
            'conversation_id' => $conversation->id,
        ];
        
        try {
            // Store draft for 7 days
            Cache::put($cacheKey, $draftData, now()->addDays(self::CACHE_TTL_DAYS));
            
            Log::debug('Draft message cached', [
                'cache_key' => $cacheKey,
                'ttl_days' => self::CACHE_TTL_DAYS
            ]);
            
            return $draftData;
        } catch (\Exception $e) {
            Log::error('Failed to save draft message to cache', [
                'conversation_id' => $conversation->id,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete draft message for a conversation and user.
     */
    public function deleteDraft(Conversation $conversation, int $userId): void
    {
        $cacheKey = $this->getDraftCacheKey($conversation, $userId);
        
        try {
            Cache::forget($cacheKey);
        } catch (\Exception $e) {
            Log::error('Failed to delete draft message from cache', [
                'conversation_id' => $conversation->id,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get the cache key for the draft message (user-specific).
     */
    private function getDraftCacheKey(Conversation $conversation, int $userId): string
    {
        return sprintf('%s:%d:user:%d', self::CACHE_KEY_PREFIX, $conversation->id, $userId);
    }

    /**
     * Clean up expired drafts (can be called by a scheduled job).
     */
    public function cleanupExpiredDrafts(): void
    {
        // This would require a more sophisticated cache implementation
        // or storing draft keys in a separate index for cleanup
        // For now, we rely on cache TTL for automatic cleanup
        
        // In a production environment, you might want to:
        // 1. Store draft keys in a Redis set with expiration
        // 2. Use a scheduled job to clean up expired keys
        // 3. Or use cache tags for bulk invalidation
        
        Log::info('Draft cleanup called - relying on cache TTL for automatic cleanup');
    }

    /**
     * Get all drafts for a user across conversations (for cleanup or migration).
     */
    public function getUserDrafts(int $userId): array
    {
        // This is a helper method that could be useful for user data export
        // or cleanup operations. In a real implementation, you'd need to
        // maintain an index of draft keys per user.
        Log::info('getUserDrafts called - not implemented without key indexing', [
            'user_id' => $userId
        ]);
        return [];
    }

    /**
     * Check if a draft exists for a conversation and user.
     */
    public function hasDraft(Conversation $conversation, int $userId): bool
    {
        $cacheKey = $this->getDraftCacheKey($conversation, $userId);
        
        try {
            return Cache::has($cacheKey);
        } catch (\Exception $e) {
            Log::error('Failed to check draft existence', [
                'conversation_id' => $conversation->id,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get draft statistics for monitoring.
     */
    public function getDraftStats(): array
    {
        // This would require cache introspection capabilities
        // For monitoring purposes in production
        return [
            'total_drafts' => 'unknown', // Would need Redis SCAN or similar
            'cache_driver' => config('cache.default'),
            'ttl_days' => self::CACHE_TTL_DAYS,
        ];
    }
}
}