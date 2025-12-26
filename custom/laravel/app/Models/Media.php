<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    // File type constants matching Chatwoot
    public const TYPE_IMAGE = 0;

    public const TYPE_AUDIO = 1;

    public const TYPE_VIDEO = 2;

    public const TYPE_FILE = 3;

    public const TYPE_LOCATION = 4;

    public const TYPE_FALLBACK = 5;

    public const TYPE_SHARE = 6;

    public const TYPE_STORY_MENTION = 7;

    public const TYPE_CONTACT = 8;

    public const TYPE_IG_REEL = 9;

    public const TYPE_IG_POST = 10;

    public const TYPE_IG_STORY = 11;

    public const TYPE_EMBED = 12;

    protected $table = 'media';

    protected $fillable = [
        'account_id',
        'mediable_type',
        'mediable_id',
        'file_type',
        'file_path',
        'file_name',
        'original_name',
        'mime_type',
        'extension',
        'file_size',
        'disk',
        'external_url',
        'thumb_path',
        'coordinates_lat',
        'coordinates_long',
        'fallback_title',
        'meta',
        'width',
        'height',
    ];

    protected $casts = [
        'file_type' => 'integer',
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'coordinates_lat' => 'float',
        'coordinates_long' => 'float',
        'meta' => 'array',
    ];

    /**
     * Get the account that owns the media.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the parent mediable model (message, contact, macro, portal, etc).
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the full URL for the file.
     */
    public function getUrlAttribute(): ?string
    {
        if ($this->external_url) {
            return $this->external_url;
        }

        if ($this->file_path) {
            $disk = $this->disk ?? config('filesystems.default');

            return Storage::disk($disk)->url($this->file_path);
        }

        return null;
    }

    /**
     * Get the thumbnail URL.
     */
    public function getThumbUrlAttribute(): ?string
    {
        if ($this->thumb_path) {
            $disk = $this->disk ?? config('filesystems.default');

            return Storage::disk($disk)->url($this->thumb_path);
        }

        return null;
    }

    /**
     * Check if media is an image.
     */
    public function isImage(): bool
    {
        return $this->file_type === self::TYPE_IMAGE;
    }

    /**
     * Check if media is audio.
     */
    public function isAudio(): bool
    {
        return $this->file_type === self::TYPE_AUDIO;
    }

    /**
     * Check if media is video.
     */
    public function isVideo(): bool
    {
        return $this->file_type === self::TYPE_VIDEO;
    }

    /**
     * Check if media is a downloadable file.
     */
    public function isFile(): bool
    {
        return $this->file_type === self::TYPE_FILE;
    }

    /**
     * Delete the physical file when model is deleted.
     */
    protected static function booted(): void
    {
        static::deleting(function (Media $media) {
            if ($media->file_path) {
                $disk = $media->disk ?? config('filesystems.default');
                Storage::disk($disk)->delete($media->file_path);

                if ($media->thumb_path) {
                    Storage::disk($disk)->delete($media->thumb_path);
                }
            }
        });
    }
}
