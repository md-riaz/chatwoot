<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Facades\Redis;

/**
 * Online Status Tracker
 * 
 * Tracks user and contact online presence and availability status using Redis.
 * Rails parity: lib/online_status_tracker.rb
 * 
 * Uses Redis sorted sets for presence tracking (with timestamps)
 * Uses Redis hashes for status tracking (online/busy/offline)
 */
class OnlineStatusTracker
{
    /**
     * Presence duration in seconds
     * Users/contacts are considered online if they've been active within this duration
     */
    public const PRESENCE_DURATION = 20; // seconds (matches Rails ENV.fetch('PRESENCE_DURATION', 20))

    /**
     * Redis key patterns
     */
    private const ONLINE_PRESENCE_USERS = 'accounts:%d:online_presence:users';
    private const ONLINE_PRESENCE_CONTACTS = 'accounts:%d:online_presence:contacts';
    private const ONLINE_STATUS = 'accounts:%d:online_status';

    /**
     * Update presence timestamp for a user or contact
     * 
     * @param int $accountId
     * @param string $objType 'User' or 'Contact'
     * @param int $objId
     * @return void
     */
    public static function updatePresence(int $accountId, string $objType, int $objId): void
    {
        $key = self::presenceKey($accountId, $objType);
        Redis::zadd($key, now()->timestamp, $objId);
    }

    /**
     * Check if a user/contact is currently present (online)
     * 
     * @param int $accountId
     * @param string $objType 'User' or 'Contact'
     * @param int $objId
     * @return bool
     */
    public static function getPresence(int $accountId, string $objType, int $objId): bool
    {
        $key = self::presenceKey($accountId, $objType);
        $connectedTime = Redis::zscore($key, $objId);

        if (!$connectedTime) {
            return false;
        }

        $threshold = now()->timestamp - self::PRESENCE_DURATION;
        return $connectedTime > $threshold;
    }

    /**
     * Get Redis key for presence tracking
     * 
     * @param int $accountId
     * @param string $type 'User' or 'Contact'
     * @return string
     */
    public static function presenceKey(int $accountId, string $type): string
    {
        return match ($type) {
            'Contact' => sprintf(self::ONLINE_PRESENCE_CONTACTS, $accountId),
            default => sprintf(self::ONLINE_PRESENCE_USERS, $accountId),
        };
    }

    /**
     * Set user availability status (online/busy/offline)
     * 
     * @param int $accountId
     * @param int $userId
     * @param string $status
     * @return void
     */
    public static function setStatus(int $accountId, int $userId, string $status): void
    {
        $key = self::statusKey($accountId);
        Redis::hset($key, $userId, $status);
    }

    /**
     * Get user availability status
     * 
     * @param int $accountId
     * @param int $userId
     * @return string|null
     */
    public static function getStatus(int $accountId, int $userId): ?string
    {
        $key = self::statusKey($accountId);
        return Redis::hget($key, $userId);
    }

    /**
     * Get Redis key for status tracking
     * 
     * @param int $accountId
     * @return string
     */
    public static function statusKey(int $accountId): string
    {
        return sprintf(self::ONLINE_STATUS, $accountId);
    }

    /**
     * Get available contact IDs (currently online)
     * 
     * @param int $accountId
     * @return array
     */
    public static function getAvailableContactIds(int $accountId): array
    {
        $key = self::presenceKey($accountId, 'Contact');
        $rangeStart = now()->timestamp - self::PRESENCE_DURATION;

        // Remove stale entries
        Redis::zremrangebyscore($key, '-inf', "({$rangeStart}");

        // Get active contact IDs
        return Redis::zrangebyscore($key, $rangeStart, '+inf');
    }

    /**
     * Get available contacts with their status
     * 
     * @param int $accountId
     * @return array ['id1' => 'online', 'id2' => 'online', ...]
     */
    public static function getAvailableContacts(int $accountId): array
    {
        $contactIds = self::getAvailableContactIds($accountId);
        return array_fill_keys($contactIds, 'online');
    }

    /**
     * Get available users with their statuses
     * 
     * Rails implementation:
     * - Gets user IDs from presence sorted set
     * - Includes users with auto_offline = false
     * - Fetches statuses from Redis hash
     * - Falls back to database availability if not in Redis
     * 
     * @param int $accountId
     * @return array ['id1' => 'online', 'id2' => 'busy', ...]
     */
    public static function getAvailableUsers(int $accountId): array
    {
        $userIds = self::getAvailableUserIds($accountId);

        if (empty($userIds)) {
            return [];
        }

        // Get statuses from Redis hash
        $statusKey = self::statusKey($accountId);
        $userAvailabilities = Redis::hmget($statusKey, $userIds);

        // Build result array with fallback to database
        $result = [];
        foreach ($userIds as $index => $userId) {
            $status = $userAvailabilities[$index];
            
            // If status not in Redis, fetch from database
            if (!$status) {
                $status = self::getAvailabilityFromDb($accountId, $userId);
            }
            
            $result[$userId] = $status;
        }

        return $result;
    }

    /**
     * Get availability from database and cache in Redis
     * 
     * @param int $accountId
     * @param int $userId
     * @return string
     */
    public static function getAvailabilityFromDb(int $accountId, int $userId): string
    {
        $account = Account::find($accountId);
        $accountUser = $account->accountUsers()->where('user_id', $userId)->first();
        
        $availability = $accountUser?->availability ?? 'offline';
        
        // Cache in Redis
        self::setStatus($accountId, $userId, $availability);
        
        return $availability;
    }

    /**
     * Get available user IDs (currently online or auto_offline = false)
     * 
     * @param int $accountId
     * @return array
     */
    public static function getAvailableUserIds(int $accountId): array
    {
        $account = Account::find($accountId);
        $key = self::presenceKey($accountId, 'User');
        $rangeStart = now()->timestamp - self::PRESENCE_DURATION;

        // Get user IDs from presence sorted set
        $userIds = Redis::zrangebyscore($key, $rangeStart, '+inf');

        // Add users with auto_offline = false
        $autoOnlineUsers = $account->accountUsers()
            ->where('auto_offline', false)
            ->pluck('user_id')
            ->map(fn($id) => (string)$id)
            ->toArray();

        // Merge and deduplicate
        $userIds = array_unique(array_merge($userIds, $autoOnlineUsers));

        return $userIds;
    }
}
