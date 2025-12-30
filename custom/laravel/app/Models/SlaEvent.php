<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'applied_sla_id',
        'conversation_id',
        'account_id',
        'sla_policy_id',
        'inbox_id',
        'event_type',
        'meta',
    ];

    protected $casts = [
        'event_type' => 'integer',
        'meta' => 'array',
    ];

    public function appliedSla(): BelongsTo
    {
        return $this->belongsTo(AppliedSla::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function slaPolicy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class);
    }

    public function inbox(): BelongsTo
    {
        return $this->belongsTo(Inbox::class);
    }
}
