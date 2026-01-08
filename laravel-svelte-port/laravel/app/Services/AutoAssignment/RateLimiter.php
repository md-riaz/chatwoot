<?php

namespace App\Services\AutoAssignment;

use App\Models\Inbox;
use App\Models\User;
use App\Models\Conversation;
use Illuminate\Support\Facades\Redis;

class RateLimiter
{
    private const ASSIGNMENT_KEY = 'ASSIGNMENT::%d::AGENT::%d::CONVERSATION::%d';
    private const ASSIGNMENT_KEY_PATTERN = 'ASSIGNMENT::%d::AGENT::%d::*';

    public function __construct(
        private Inbox $inbox,
        private User $agent
    ) {}

    public function withinLimit(): bool
    {
        if (!$this->enabled()) {
            return true;
        }

        return $this->currentCount() < $this->limit();
    }

    public function trackAssignment(Conversation $conversation): void
    {
        if (!$this->enabled()) {
            return;
        }

        $assignmentKey = sprintf(
            self::ASSIGNMENT_KEY,
            $this->inbox->id,
            $this->agent->id,
            $conversation->id
        );

        Redis::setex($assignmentKey, $this->window(), $conversation->id);
    }

    public function currentCount(): int
    {
        if (!$this->enabled()) {
            return 0;
        }

        $pattern = sprintf(
            self::ASSIGNMENT_KEY_PATTERN,
            $this->inbox->id,
            $this->agent->id
        );

        $keys = Redis::keys($pattern);
        return count($keys);
    }

    private function enabled(): bool
    {
        $limit = $this->limit();
        return $limit !== null && $limit > 0;
    }

    private function limit(): ?int
    {
        $config = $this->config();
        return $config?->fair_distribution_limit;
    }

    private function window(): int
    {
        $config = $this->config();
        return $config?->fair_distribution_window ?? 86400; // 24 hours default
    }

    private function config()
    {
        return $this->inbox->assignmentPolicy;
    }
}