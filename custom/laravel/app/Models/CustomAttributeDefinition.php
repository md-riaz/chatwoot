<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomAttributeDefinition extends Model
{
    use HasFactory;

    // Attribute model constants
    public const MODEL_CONVERSATION = 0;
    public const MODEL_CONTACT = 1;

    // Attribute display type constants
    public const DISPLAY_TEXT = 0;
    public const DISPLAY_NUMBER = 1;
    public const DISPLAY_CURRENCY = 2;
    public const DISPLAY_PERCENT = 3;
    public const DISPLAY_LINK = 4;
    public const DISPLAY_DATE = 5;
    public const DISPLAY_LIST = 6;
    public const DISPLAY_CHECKBOX = 7;

    // Standard attributes that cannot be used as custom keys
    public const STANDARD_ATTRIBUTES = [
        'conversation' => [
            'status', 'priority', 'assignee_id', 'inbox_id', 'team_id', 'display_id',
            'campaign_id', 'labels', 'browser_language', 'country_code', 'referer',
            'created_at', 'last_activity_at',
        ],
        'contact' => [
            'name', 'email', 'phone_number', 'identifier', 'country_code', 'city',
            'created_at', 'last_activity_at', 'referer', 'blocked',
        ],
    ];

    protected $fillable = [
        'account_id',
        'attribute_key',
        'attribute_display_name',
        'attribute_description',
        'attribute_model',
        'attribute_display_type',
        'attribute_values',
        'default_value',
        'regex_pattern',
        'regex_cue',
    ];

    protected $casts = [
        'attribute_model' => 'integer',
        'attribute_display_type' => 'integer',
        'attribute_values' => 'array',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Scope for conversation attributes.
     */
    public function scopeConversationAttributes($query)
    {
        return $query->where('attribute_model', self::MODEL_CONVERSATION);
    }

    /**
     * Scope for contact attributes.
     */
    public function scopeContactAttributes($query)
    {
        return $query->where('attribute_model', self::MODEL_CONTACT);
    }

    /**
     * Check if the attribute key conflicts with standard attributes.
     */
    public function keyConflictsWithStandard(): bool
    {
        $modelKey = $this->attribute_model === self::MODEL_CONVERSATION ? 'conversation' : 'contact';
        return in_array($this->attribute_key, self::STANDARD_ATTRIBUTES[$modelKey]);
    }
}
