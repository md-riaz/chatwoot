<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Portal extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'channel_web_widget_id',
        'name',
        'slug',
        'custom_domain',
        'color',
        'homepage_link',
        'page_title',
        'header_text',
        'archived',
        'config',
        'ssl_settings',
    ];

    protected $casts = [
        'archived' => 'boolean',
        'config' => 'array',
        'ssl_settings' => 'array',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function channelWebWidget(): BelongsTo
    {
        // Note: References WebWidget channel if connected
        return $this->belongsTo(\App\Models\Channels\WebWidget::class, 'channel_web_widget_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function inboxes(): HasMany
    {
        return $this->hasMany(Inbox::class);
    }

    /**
     * Get all media files for this portal (logo).
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Get the default locale for the portal.
     */
    public function getDefaultLocaleAttribute(): string
    {
        return $this->config['default_locale'] ?? 'en';
    }

    /**
     * Scope for active (non-archived) portals.
     */
    public function scopeActive($query)
    {
        return $query->where('archived', false);
    }
}
