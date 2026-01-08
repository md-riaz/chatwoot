<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Avatarable trait for models that can have avatars
 * Provides Rails-like avatar functionality for Laravel models
 */
trait Avatarable
{
    /**
     * Upload and store avatar file
     */
    public function uploadAvatar(UploadedFile $file): string
    {
        // Validate file
        $this->validateAvatarFile($file);
        
        // Delete old avatar if exists
        $this->deleteAvatar();
        
        // Generate unique filename
        $filename = time() . '_' . Str::slug($this->name ?? 'avatar') . '.' . $file->getClientOriginalExtension();
        
        // Store file
        $path = $file->storeAs('avatars', $filename, 'public');
        
        // Update avatar_url
        $avatarUrl = '/storage/' . $path;
        $this->update(['avatar_url' => $avatarUrl]);
        
        return $avatarUrl;
    }
    
    /**
     * Delete current avatar
     */
    public function deleteAvatar(): bool
    {
        if ($this->avatar_url) {
            // Extract path from URL
            $path = str_replace('/storage/', '', $this->avatar_url);
            
            // Delete file from storage
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            
            // Clear avatar_url
            $this->update(['avatar_url' => null]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Get avatar URL with fallback to Gravatar
     */
    public function getAvatarUrlAttribute($value): ?string
    {
        if ($value) {
            // Convert relative URLs to absolute URLs for cross-origin access
            if (str_starts_with($value, '/storage/')) {
                return url($value);
            }
            return $value;
        }
        
        // Fallback to Gravatar if email exists and no avatar is set
        if ($this->email && !config('app.disable_gravatar', false)) {
            $hash = md5(strtolower(trim($this->email)));
            return "https://www.gravatar.com/avatar/{$hash}?d=404&s=250";
        }
        
        return null;
    }
    
    /**
     * Validate avatar file
     */
    protected function validateAvatarFile(UploadedFile $file): void
    {
        // Check file size (max 15MB like Rails)
        if ($file->getSize() > 15 * 1024 * 1024) {
            throw new \InvalidArgumentException('Avatar file is too large. Maximum size is 15MB.');
        }
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            throw new \InvalidArgumentException('Avatar file type not supported. Only JPEG, PNG, and GIF are allowed.');
        }
    }
    
    /**
     * Check if avatar is attached
     */
    public function hasAvatar(): bool
    {
        return !empty($this->avatar_url);
    }
    
    /**
     * Get avatar URL for API responses (Rails compatibility)
     */
    public function getApiAvatarUrl(): string
    {
        // Use the accessor which handles absolute URL conversion and Gravatar fallback
        return $this->avatar_url ?? '';
    }
}