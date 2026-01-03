<?php

namespace App\Models\Concerns;

trait AccountCacheRevalidator
{
    protected static function bootAccountCacheRevalidator()
    {
        static::created(function ($model) {
            $model->updateAccountCache();
        });
        
        static::updated(function ($model) {
            $model->updateAccountCache();
        });
        
        static::deleted(function ($model) {
            $model->updateAccountCache();
        });
    }

    public function updateAccountCache(): void
    {
        if ($this->account) {
            $modelKey = strtolower(class_basename($this));
            $this->account->updateCacheKey($modelKey);
        }
    }
}