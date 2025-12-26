<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportingEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'conversation_id',
        'inbox_id',
        'user_id',
        'name',
        'value',
        'value_in_business_hours',
        'event_start_time',
        'event_end_time',
    ];

    protected $casts = [
        'value' => 'float',
        'value_in_business_hours' => 'float',
        'event_start_time' => 'datetime',
        'event_end_time' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function inbox(): BelongsTo
    {
        return $this->belongsTo(Inbox::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeFilterByDateRange($query, $range)
    {
        if ($range) {
            return $query->whereBetween('created_at', $range);
        }
        return $query;
    }

    /**
     * Scope to filter by inbox.
     */
    public function scopeFilterByInbox($query, $inboxId)
    {
        if ($inboxId) {
            return $query->where('inbox_id', $inboxId);
        }
        return $query;
    }

    /**
     * Scope to filter by user.
     */
    public function scopeFilterByUser($query, $userId)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }
        return $query;
    }

    /**
     * Scope to filter by event name.
     */
    public function scopeFilterByName($query, $name)
    {
        if ($name) {
            return $query->where('name', $name);
        }
        return $query;
    }
}
