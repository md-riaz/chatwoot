<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomFilter extends Model
{
    use HasFactory;

    // Filter type constants
    public const TYPE_CONVERSATION = 0;

    public const TYPE_CONTACT = 1;

    public const TYPE_REPORT = 2;

    protected $fillable = [
        'account_id',
        'user_id',
        'name',
        'filter_type',
        'query',
    ];

    protected $casts = [
        'filter_type' => 'integer',
        'query' => 'array',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for conversation filters.
     */
    public function scopeConversationFilters($query)
    {
        return $query->where('filter_type', self::TYPE_CONVERSATION);
    }

    /**
     * Scope for contact filters.
     */
    public function scopeContactFilters($query)
    {
        return $query->where('filter_type', self::TYPE_CONTACT);
    }

    /**
     * Scope for report filters.
     */
    public function scopeReportFilters($query)
    {
        return $query->where('filter_type', self::TYPE_REPORT);
    }
}
