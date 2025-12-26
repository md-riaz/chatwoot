<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CsatSurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'conversation_id',
        'contact_id',
        'message_id',
        'assigned_agent_id',
        'rating',
        'feedback_message',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
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
     * Scope to filter by assigned agent.
     */
    public function scopeFilterByAgent($query, $userIds)
    {
        if ($userIds) {
            return $query->whereIn('assigned_agent_id', (array) $userIds);
        }

        return $query;
    }

    /**
     * Scope to filter by rating.
     */
    public function scopeFilterByRating($query, $rating)
    {
        if ($rating !== null) {
            return $query->where('rating', $rating);
        }

        return $query;
    }
}
