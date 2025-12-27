<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboxCapacityLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_capacity_policy_id',
        'inbox_id',
        'conversation_limit',
    ];

    protected $casts = [
        'conversation_limit' => 'integer',
    ];

    public function agentCapacityPolicy(): BelongsTo
    {
        return $this->belongsTo(AgentCapacityPolicy::class);
    }

    public function inbox(): BelongsTo
    {
        return $this->belongsTo(Inbox::class);
    }
}
