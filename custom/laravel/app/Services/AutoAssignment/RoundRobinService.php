<?php

namespace App\Services\AutoAssignment;

use App\Models\Inbox;
use App\Models\User;
use Illuminate\Support\Facades\Redis;

class RoundRobinService
{
    private const ROUND_ROBIN_KEY = 'ROUND_ROBIN_AGENTS:%d';

    public function __construct(
        private Inbox $inbox
    ) {}

    public function selectAgent(array $allowedAgentIds): ?User
    {
        if (empty($allowedAgentIds)) {
            return null;
        }

        $key = sprintf(self::ROUND_ROBIN_KEY, $this->inbox->id);
        $queue = $this->getQueue($key);

        // If queue is empty or doesn't match current agents, rebuild it
        if (empty($queue) || !$this->queueMatchesAgents($queue, $allowedAgentIds)) {
            $this->rebuildQueue($key, $allowedAgentIds);
            $queue = $this->getQueue($key);
        }

        // Get next agent from queue
        $nextAgentId = Redis::lpop($key);
        if (!$nextAgentId) {
            return null;
        }

        // Add agent back to end of queue
        Redis::rpush($key, $nextAgentId);

        return User::find($nextAgentId);
    }

    public function clearQueue(): void
    {
        $key = sprintf(self::ROUND_ROBIN_KEY, $this->inbox->id);
        Redis::del($key);
    }

    private function getQueue(string $key): array
    {
        return Redis::lrange($key, 0, -1) ?: [];
    }

    private function queueMatchesAgents(array $queue, array $allowedAgentIds): bool
    {
        $queueSet = array_unique($queue);
        $allowedSet = array_unique($allowedAgentIds);
        
        sort($queueSet);
        sort($allowedSet);
        
        return $queueSet === $allowedSet;
    }

    private function rebuildQueue(string $key, array $allowedAgentIds): void
    {
        Redis::del($key);
        
        if (!empty($allowedAgentIds)) {
            // Shuffle for fairness on rebuild
            shuffle($allowedAgentIds);
            Redis::rpush($key, ...$allowedAgentIds);
            
            // Set expiry to prevent stale queues
            Redis::expire($key, 86400); // 24 hours
        }
    }
}