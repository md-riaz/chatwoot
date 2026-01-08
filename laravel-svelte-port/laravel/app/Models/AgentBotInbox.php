<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentBotInbox extends Model
{
    use HasFactory;

    protected $fillable = [
        'inbox_id',
        'agent_bot_id',
    ];

    /**
     * Get the inbox.
     */
    public function inbox(): BelongsTo
    {
        return $this->belongsTo(Inbox::class);
    }

    /**
     * Get the agent bot.
     */
    public function agentBot(): BelongsTo
    {
        return $this->belongsTo(AgentBot::class);
    }
}
