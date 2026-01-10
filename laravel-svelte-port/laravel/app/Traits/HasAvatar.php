<?php

namespace App\Traits;

use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Http\UploadedFile;

/**
 * Laravel-native avatar management using Spatie Media Library
 * 
 * This trait provides clean, Laravel-standard avatar functionality:
 * - Uses Spatie Media Library (Laravel ecosystem standard)
 * - Automatic image conversions and variants
 * - Built-in optimization and validation
 * - Clean API with Laravel conventions
 * - Integrates with Laravel's file storage system
 */
trait HasAvatar
{
    use InteractsWithMedia;

    /**
     * Boot the trait
     */
    protected static function bootHasAvatar()
    {
        // Auto-fetch Gravatar when email changes (if enabled)
        static::saved(function ($model) {
            if ($model->wasChanged('email') && $model->email && !$model->hasMedia('avatar')) {
                if (config('app.auto_fetch_gravatar', true)) {
                    dispatch(function () use ($model) {
                        $model->fetchGravatarAvatar();
                    })->delay(now()->addSeconds(30));
                }
            }
        });
    }

    /**
     * Define media collections (Laravel Media Library pattern)
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile() // Only one avatar per model
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    /**
     * Define media conversions (automatic variants)
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(64)
            ->height(64)
            ->sharpen(10)
            ->optimize()
            ->performOnCollections('avatar');

        $this->addMediaConversion('medium')
            ->width(250)
            ->height(250)
            ->sharpen(10)
            ->optimize()
            ->performOnCollections('avatar');

        $this->addMediaConversion('large')
            ->width(500)
            ->height(500)
            ->sharpen(10)
            ->optimize()
            ->performOnCollections('avatar');
    }

    /**
     * Upload avatar (Laravel-native way)
     */
    public function uploadAvatar(UploadedFile $file): Media
    {
        return $this->addMedia($file)
            ->usingFileName($this->generateAvatarFileName($file))
            ->toMediaCollection('avatar');
    }

    /**
     * Get avatar URL with size variant
     */
    public function getAvatarUrl(string $conversion = 'medium'): string
    {
        $media = $this->getFirstMedia('avatar');
        
        if ($media) {
            $url = $media->getUrl($conversion);
            
            // Ensure absolute URL for API responses
            if (!str_starts_with($url, 'http')) {
                $url = config('app.url') . $url;
            }
            
            return $url;
        }

        // Fallback to Gravatar if email exists
        $gravatarUrl = $this->getGravatarUrl();
        if ($gravatarUrl) {
            return $gravatarUrl;
        }

        // Final fallback to default avatar
        return $this->getDefaultAvatarUrl();
    }

    /**
     * Get default avatar URL when no avatar or Gravatar is available
     */
    public function getDefaultAvatarUrl(): string
    {
        // Return empty string - let frontend handle fallback display
        return '';
    }

    /**
     * Get Gravatar URL
     */
    public function getGravatarUrl(int $size = 250): string
    {
        if (!$this->email || config('app.disable_gravatar', false)) {
            return '';
        }

        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=404&s={$size}";
    }

    /**
     * Check if avatar exists
     */
    public function hasAvatar(): bool
    {
        return $this->hasMedia('avatar');
    }

    /**
     * Delete avatar
     */
    public function deleteAvatar(): bool
    {
        $this->clearMediaCollection('avatar');
        return true;
    }

    /**
     * Fetch avatar from Gravatar (simple HTTP check + download)
     */
    public function fetchGravatarAvatar(): bool
    {
        if (!$this->email || config('app.disable_gravatar', false)) {
            return false;
        }

        $gravatarUrl = $this->getGravatarUrl(500); // Get large version
        
        // Simple HTTP check if Gravatar exists
        $headers = @get_headers($gravatarUrl);
        if (!$headers || strpos($headers[0], '404') !== false) {
            return false;
        }

        try {
            // Download and add to media collection
            $this->addMediaFromUrl($gravatarUrl)
                ->usingFileName($this->generateAvatarFileName())
                ->toMediaCollection('avatar');
            
            return true;
        } catch (\Exception $e) {
            \Log::info("Gravatar fetch failed for {$this->email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate avatar filename
     */
    protected function generateAvatarFileName(?UploadedFile $file = null): string
    {
        $extension = $file ? $file->getClientOriginalExtension() : 'jpg';
        $modelName = class_basename($this);
        $identifier = $this->id ?? uniqid();
        
        return "{$modelName}_{$identifier}_avatar." . $extension;
    }

    /**
     * Laravel accessor for avatar_url attribute
     */
    public function getAvatarUrlAttribute(): string
    {
        return $this->getAvatarUrl();
    }

    /**
     * API-friendly avatar URL
     */
    public function getApiAvatarUrl(): string
    {
        return $this->getAvatarUrl();
    }
}