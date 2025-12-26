<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationHook extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'integration_id',
        'inbox_id',
        'app_id',
        'hook_type',
        'status',
        'settings',
        'reference_id',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    public function inbox(): BelongsTo
    {
        return $this->belongsTo(Inbox::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('status', 'enabled');
    }

    public function scopeForInbox($query, int $inboxId)
    {
        return $query->where('inbox_id', $inboxId);
    }
}
