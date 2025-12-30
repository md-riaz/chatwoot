<?php

namespace App\Services;

use App\Models\Message;

/**
 * Minimal Search service adapter.
 * - Default driver is DB (Eloquent LIKE searches)
 * - Provides index/remove hooks for future drivers
 */
class SearchService
{
    public function indexMessage(Message|array $message): void
    {
        // For DB driver we don't need to index; for external drivers this method should push data.
        // Intentionally a no-op to keep message creation fast and tests simple.
    }

    public function removeMessage(int $messageId): void
    {
        // No-op for DB driver.
    }

    /**
     * Search messages using DB fallback. Returns array of messages.
     * Options: 'limit' (int), 'account_id'
     */
    public function search(string $q, array $options = []): array
    {
        $limit = $options['limit'] ?? 50;

        $query = Message::query()->where('content', 'like', "%{$q}%");

        if (isset($options['account_id'])) {
            $query->where('account_id', $options['account_id']);
        }

        return $query->limit($limit)->get()->toArray();
    }
}
