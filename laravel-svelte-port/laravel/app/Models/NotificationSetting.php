<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'user_id',
        'email_flags',
        'push_flags',
    ];

    protected $casts = [
        'email_flags' => 'integer',
        'push_flags' => 'integer',
    ];

    // Notification types (matches Rails implementation)
    public const NOTIFICATION_TYPES = [
        'conversation_creation' => 1,
        'conversation_assignment' => 2,
        'assigned_conversation_new_message' => 4,
        'conversation_mention' => 8,
        'participating_conversation_new_message' => 16,
        'sla_missed_first_response' => 32,
        'sla_missed_next_response' => 64,
        'sla_missed_resolution' => 128,
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSelectedEmailFlagsAttribute(): array
    {
        return $this->getFlagsFor('email_flags');
    }

    public function getSelectedPushFlagsAttribute(): array
    {
        return $this->getFlagsFor('push_flags');
    }

    public function getAllEmailFlagsAttribute(): array
    {
        return array_keys(self::NOTIFICATION_TYPES);
    }

    public function getAllPushFlagsAttribute(): array
    {
        return array_keys(self::NOTIFICATION_TYPES);
    }

    public function setSelectedEmailFlagsAttribute(?array $flags): void
    {
        $this->email_flags = $this->calculateFlagValue($flags ?? []);
    }

    public function setSelectedPushFlagsAttribute(?array $flags): void
    {
        $this->push_flags = $this->calculateFlagValue($flags ?? []);
    }

    private function getFlagsFor(string $column): array
    {
        $value = $this->{$column};
        $flags = [];

        foreach (self::NOTIFICATION_TYPES as $name => $bitValue) {
            if ($value & $bitValue) {
                $flags[] = $name;
            }
        }

        return $flags;
    }

    private function calculateFlagValue(array $flags): int
    {
        $value = 0;

        foreach ($flags as $flag) {
            if (isset(self::NOTIFICATION_TYPES[$flag])) {
                $value |= self::NOTIFICATION_TYPES[$flag];
            }
        }

        return $value;
    }
}
