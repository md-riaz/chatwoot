<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportingEvent extends Model
{
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

    // Scopes for filtering
    public function scopeFilterByDateRange($query, $range)
    {
        if ($range) {
            return $query->whereBetween('created_at', $range);
        }
        
        return $query;
    }

    public function scopeFilterByInboxId($query, $inboxId)
    {
        if ($inboxId) {
            return $query->where('inbox_id', $inboxId);
        }
        
        return $query;
    }

    public function scopeFilterByUserId($query, $userId)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }
        
        return $query;
    }

    public function scopeFilterByName($query, $name)
    {
        if ($name) {
            return $query->where('name', $name);
        }
        
        return $query;
    }
}