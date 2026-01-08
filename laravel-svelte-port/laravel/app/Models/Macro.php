<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Macro extends Model
{
    use HasFactory;

    // Visibility constants
    public const VISIBILITY_PERSONAL = 0;

    public const VISIBILITY_GLOBAL = 1;

    // Supported actions
    public const ACTIONS = [
        'send_message',
        'add_label',
        'assign_team',
        'assign_agent',
        'mute_conversation',
        'change_status',
        'remove_label',
        'remove_assigned_team',
        'resolve_conversation',
        'snooze_conversation',
        'change_priority',
        'send_email_transcript',
        'send_attachment',
        'add_private_note',
        'send_webhook_event',
    ];

    protected $fillable = [
        'account_id',
        'name',
        'visibility',
        'actions',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'visibility' => 'integer',
        'actions' => 'array',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    /**
     * Get all media files for this macro.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function isPersonal(): bool
    {
        return $this->visibility === self::VISIBILITY_PERSONAL;
    }

    public function isGlobal(): bool
    {
        return $this->visibility === self::VISIBILITY_GLOBAL;
    }

    /**
     * Scope for global macros.
     */
    public function scopeGlobal($query)
    {
        return $query->where('visibility', self::VISIBILITY_GLOBAL);
    }

    /**
     * Scope for personal macros.
     */
    public function scopePersonal($query)
    {
        return $query->where('visibility', self::VISIBILITY_PERSONAL);
    }
}
