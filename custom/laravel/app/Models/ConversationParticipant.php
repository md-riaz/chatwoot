<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class ConversationParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'conversation_id',
        'user_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (ConversationParticipant $participant) {
            if (! $participant->account_id && $participant->conversation) {
                $participant->account_id = $participant->conversation->account_id;
            }
        });

        static::saving(function (ConversationParticipant $participant) {
            $participant->validateRequiredFields();
            $participant->validateUniqueness();
            $participant->validateInboxAccess();
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Validate required fields like Rails validations
     */
    protected function validateRequiredFields(): void
    {
        if (! $this->account_id) {
            throw ValidationException::withMessages([
                'account_id' => ['Account ID is required'],
            ]);
        }

        if (! $this->conversation_id) {
            throw ValidationException::withMessages([
                'conversation_id' => ['Conversation ID is required'],
            ]);
        }

        if (! $this->user_id) {
            throw ValidationException::withMessages([
                'user_id' => ['User ID is required'],
            ]);
        }
    }

    /**
     * Validate uniqueness like Rails validates :user_id, uniqueness: { scope: [:conversation_id] }
     */
    protected function validateUniqueness(): void
    {
        $exists = static::where('user_id', $this->user_id)
            ->where('conversation_id', $this->conversation_id)
            ->where('id', '!=', $this->id ?? 0)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'user_id' => ['User is already a participant in this conversation'],
            ]);
        }
    }

    /**
     * Validate that the user has access to the conversation's inbox
     * Equivalent to Rails ensure_inbox_access validation
     */
    protected function validateInboxAccess(): void
    {
        if (! $this->conversation || ! $this->user) {
            return;
        }

        // Check if user has access to the inbox through account membership
        $hasInboxAccess = $this->conversation->inbox
            ->account
            ->users()
            ->where('users.id', $this->user_id)
            ->exists();

        if (! $hasInboxAccess) {
            throw ValidationException::withMessages([
                'user_id' => ['User must have inbox access'],
            ]);
        }
    }
}
