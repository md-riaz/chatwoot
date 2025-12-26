<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlaPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'name',
        'description',
        'first_response_time_threshold',
        'next_response_time_threshold',
        'resolution_time_threshold',
        'only_during_business_hours',
        'active',
    ];

    protected $casts = [
        'first_response_time_threshold' => 'integer',
        'next_response_time_threshold' => 'integer',
        'resolution_time_threshold' => 'integer',
        'only_during_business_hours' => 'boolean',
        'active' => 'boolean',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function appliedSlas(): HasMany
    {
        return $this->hasMany(AppliedSla::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function isBreached(Conversation $conversation): array
    {
        $breaches = [];

        if ($this->first_response_time_threshold && $conversation->first_reply_created_at) {
            $firstResponseTime = $conversation->created_at->diffInSeconds($conversation->first_reply_created_at);
            if ($firstResponseTime > $this->first_response_time_threshold) {
                $breaches['first_response'] = [
                    'threshold' => $this->first_response_time_threshold,
                    'actual' => $firstResponseTime,
                    'exceeded_by' => $firstResponseTime - $this->first_response_time_threshold,
                ];
            }
        }

        return $breaches;
    }
}
