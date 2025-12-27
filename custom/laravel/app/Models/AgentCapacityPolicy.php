<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentCapacityPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'name',
        'description',
        'exclusion_rules',
    ];

    protected $casts = [
        'exclusion_rules' => 'array',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function inboxCapacityLimits(): HasMany
    {
        return $this->hasMany(InboxCapacityLimit::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(AccountUser::class);
    }
}
