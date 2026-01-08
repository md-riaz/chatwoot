<?php

namespace App\Traits;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendReauthorizationNotificationJob;

trait Reauthorizable
{
    /**
     * Default authorization error threshold
     */
    public const AUTHORIZATION_ERROR_THRESHOLD = 2;

    /**
     * Check if reauthorization is required
     */
    public function reauthorizationRequired(): bool
    {
        return Redis::exists($this->reauthorizationRequiredKey());
    }

    /**
     * Get current authorization error count
     */
    public function authorizationErrorCount(): int
    {
        return (int) Redis::get($this->authorizationErrorCountKey()) ?: 0;
    }

    /**
     * Increment authorization error count and check threshold
     */
    public function authorizationError(): void
    {
        Redis::incr($this->authorizationErrorCountKey());
        
        $threshold = defined('static::AUTHORIZATION_ERROR_THRESHOLD') 
            ? static::AUTHORIZATION_ERROR_THRESHOLD 
            : self::AUTHORIZATION_ERROR_THRESHOLD;

        if ($this->authorizationErrorCount() >= $threshold) {
            $this->promptReauthorization();
        }
    }

    /**
     * Mark channel as requiring reauthorization
     */
    public function promptReauthorization(): void
    {
        Redis::set($this->reauthorizationRequiredKey(), true);
        
        // Send notification based on channel type
        $this->sendReauthorizationNotification();
        
        // Invalidate inbox cache if applicable
        if (method_exists($this, 'inbox') && $this->inbox) {
            $this->inbox->touch();
        }
    }

    /**
     * Clear reauthorization flags after successful reauth
     */
    public function reauthorized(): void
    {
        Redis::del($this->authorizationErrorCountKey());
        Redis::del($this->reauthorizationRequiredKey());
        
        if (method_exists($this, 'inbox') && $this->inbox) {
            $this->inbox->touch();
        }
    }

    /**
     * Send reauthorization notification
     */
    protected function sendReauthorizationNotification(): void
    {
        $channelType = class_basename($this);
        
        try {
            SendReauthorizationNotificationJob::dispatch($this, $channelType);
        } catch (\Exception $e) {
            Log::error('Failed to dispatch reauthorization notification', [
                'channel_id' => $this->id,
                'channel_type' => $channelType,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get Redis key for authorization error count
     */
    protected function authorizationErrorCountKey(): string
    {
        return sprintf(
            'AUTHORIZATION_ERROR_COUNT:%s:%d',
            class_basename($this),
            $this->id
        );
    }

    /**
     * Get Redis key for reauthorization required flag
     */
    protected function reauthorizationRequiredKey(): string
    {
        return sprintf(
            'REAUTHORIZATION_REQUIRED:%s:%d',
            class_basename($this),
            $this->id
        );
    }
}
