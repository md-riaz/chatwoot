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
     * Accessor: Return filter_type as a string for API responses.
     */
    public function getFilterTypeAttribute($value): string
    {
        switch ((int) $value) {
            case self::TYPE_CONTACT:
                return 'contact';
            case self::TYPE_REPORT:
                return 'report';
            case self::TYPE_CONVERSATION:
            default:
                return 'conversation';
        }
    }

    /**
     * Mutator: Accept string or int and store as integer.
     */
    public function setFilterTypeAttribute($value): void
    {
        if (is_numeric($value)) {
            $this->attributes['filter_type'] = (int) $value;
            return;
        }

        switch (strtolower((string) $value)) {
            case 'contact':
                $this->attributes['filter_type'] = self::TYPE_CONTACT;
                break;
            case 'report':
                $this->attributes['filter_type'] = self::TYPE_REPORT;
                break;
            case 'conversation':
            default:
                $this->attributes['filter_type'] = self::TYPE_CONVERSATION;
                break;
        }
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
