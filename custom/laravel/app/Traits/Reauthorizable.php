<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait Reauthorizable
{
    public function authorizationError(): void
    {
        $key = $this->authorizationErrorCountKey();
        $count = Cache::increment($key);
        $threshold = defined(class_basename($this) . '::AUTHORIZATION_ERROR_THRESHOLD') ? constant(class_basename($this) . '::AUTHORIZATION_ERROR_THRESHOLD') : ($this->authorization_error_threshold ?? 2);

        if ($count >= $threshold) {
            $this->promptReauthorization();
            // reset counter
            Cache::forget($key);
        }
    }

    public function authorizationErrorCount(): int
    {
        return (int) Cache::get($this->authorizationErrorCountKey(), 0);
    }

    protected function authorizationErrorCountKey(): string
    {
        return sprintf('AUTHORIZATION_ERROR_COUNT:%s:%s', $this->getTable(), $this->getKey());
    }

    protected function promptReauthorization(): void
    {
        // Default behaviour: mark an attribute if exists, otherwise set a cache flag.
        if ($this->getConnection()->getSchemaBuilder()->hasColumn($this->getTable(), 'active')) {
            try {
                $this->update(['active' => false]);
            } catch (\Exception $e) {
                // swallow
            }
        }

        Cache::put(sprintf('AUTHORIZATION_PROMPTED:%s:%s', $this->getTable(), $this->getKey()), true, now()->addDays(7));
    }
}
