<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Attachment extends Model
{
    use HasFactory;

    // File type constants
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

    protected $fillable = [
        'message_id',
        'account_id',
        'file_type',
        'external_url',
        'coordinates_lat',
        'coordinates_long',
        'fallback_title',
        'extension',
        'meta',
    ];

    protected $casts = [
        'file_type' => 'integer',
        'coordinates_lat' => 'float',
        'coordinates_long' => 'float',
        'meta' => 'array',
    ];

    /**
     * Get the message that owns the attachment.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get the account that owns the attachment.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get all media files for this attachment.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Check if attachment is an image.
     */
    public function isImage(): bool
    {
        return $this->file_type === self::TYPE_IMAGE;
    }

    /**
     * Check if attachment is audio.
     */
    public function isAudio(): bool
    {
        return $this->file_type === self::TYPE_AUDIO;
    }

    /**
     * Check if attachment is video.
     */
    public function isVideo(): bool
    {
        return $this->file_type === self::TYPE_VIDEO;
    }

    /**
     * Check if attachment is a downloadable file.
     */
    public function isFile(): bool
    {
        return $this->file_type === self::TYPE_FILE;
    }

    /**
     * Check if attachment is a location.
     */
    public function isLocation(): bool
    {
        return $this->file_type === self::TYPE_LOCATION;
    }

    protected static function booted()
    {
        static::created(function (self $attachment) {
            if ($attachment->isAudio()) {
                // Dispatch transcription job for audio attachments
                \App\Jobs\Message\AudioTranscriptionJob::dispatch($attachment->id)->onQueue('low');
            }
        });
    }
}
