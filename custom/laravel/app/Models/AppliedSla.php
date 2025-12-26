<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppliedSla extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'sla_policy_id',
        'conversation_id',
        'sla_first_response_at',
        'sla_next_response_at',
        'sla_resolution_at',
    ];

    protected $casts = [
        'sla_first_response_at' => 'datetime',
        'sla_next_response_at' => 'datetime',
        'sla_resolution_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function slaPolicy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
