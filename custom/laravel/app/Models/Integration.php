<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Integration extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'type',
        'settings',
        'credentials',
        'active',
    ];

    protected $casts = [
        'settings' => 'array',
        'credentials' => 'encrypted:array',
        'active' => 'boolean',
    ];

    protected $hidden = [
        'credentials',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function hooks(): HasMany
    {
        return $this->hasMany(IntegrationHook::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public static function types(): array
    {
        return [
            'slack',
            'linear',
            'dialogflow',
            'openai',
            'shopify',
            'google_translate',
            'dyte',
            'captain',
        ];
    }
}
