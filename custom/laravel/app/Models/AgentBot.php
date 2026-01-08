<?php

namespace App\Models;

use App\Models\Concerns\AccessTokenable;
use App\Traits\Avatarable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class AgentBot extends Model
{
    use HasFactory, AccessTokenable, Avatarable;

    // Bot type constants
    public const TYPE_WEBHOOK = 0;

    protected $fillable = [
        'account_id',
        'name',
        'description',
        'outgoing_url',
        'bot_type',
        'bot_config',
        'avatar_url',
    ];

    protected $casts = [
        'bot_type' => 'integer',
        'bot_config' => 'array',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function inboxes(): BelongsToMany
    {
        return $this->belongsToMany(Inbox::class, 'agent_bot_inboxes')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Get messages sent by this bot.
     */
    public function messages(): MorphMany
    {
        return $this->morphMany(Message::class, 'sender');
    }

    /**
     * Get conversations assigned to this bot.
     * Note: Requires 'assignee_agent_bot_id' column in conversations table (optional feature).
     */
    public function assignedConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'assignee_id')
            ->where('assignee_type', self::class);
    }

    /**
     * Check if this is a system bot (no account).
     */
    public function isSystemBot(): bool
    {
        return $this->account_id === null;
    }

    /**
     * Scope for accessible bots (global or belonging to account).
     */
    public function scopeAccessibleTo($query, ?Account $account)
    {
        $accountId = $account?->id;

        return $query->whereNull('account_id')->orWhere('account_id', $accountId);
    }
}
