<?php

namespace App\Models\Channels;

use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;

class Api extends Model
{
    use HasFactory;

    protected $table = 'channel_api';

    protected $fillable = [
        'account_id',
        'webhook_url',
        'identifier',
        'hmac_token',
        'hmac_mandatory',
        'additional_attributes',
    ];

    protected $hidden = [
        'hmac_token',
    ];

    protected $casts = [
        'hmac_mandatory' => 'boolean',
        'additional_attributes' => 'array',
    ];

    /**
     * Boot the model and generate secure tokens.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->identifier)) {
                $model->identifier = $model->generateUniqueToken('identifier');
            }
            if (empty($model->hmac_token)) {
                $model->hmac_token = $model->generateUniqueToken('hmac_token');
            }
        });
    }

    /**
     * Get the inbox for this channel.
     */
    public function inbox(): MorphOne
    {
        return $this->morphOne(Inbox::class, 'channel');
    }

    /**
     * Get the channel name.
     */
    public function getName(): string
    {
        return 'API';
    }

    /**
     * Generate a unique token for the specified field.
     */
    protected function generateUniqueToken(string $field): string
    {
        do {
            $token = Str::random(40);
        } while (static::where($field, $token)->exists());

        return $token;
    }

    /**
     * Validate webhook URL length.
     */
    public function validateWebhookUrl(): bool
    {
        if (empty($this->webhook_url)) {
            return true; // Allow empty webhook URLs
        }

        return strlen($this->webhook_url) <= 2048; // URL length limit
    }

    /**
     * Validate agent reply time window.
     */
    public function validateAgentReplyTimeWindow(): bool
    {
        $timeWindow = $this->additional_attributes['agent_reply_time_window'] ?? null;
        
        if (empty($timeWindow)) {
            return true; // Allow empty time window
        }

        return is_numeric($timeWindow) && (int)$timeWindow > 0;
    }

    /**
     * Get the agent reply time window in minutes.
     */
    public function getAgentReplyTimeWindow(): ?int
    {
        $timeWindow = $this->additional_attributes['agent_reply_time_window'] ?? null;
        
        return $timeWindow ? (int)$timeWindow : null;
    }

    /**
     * Verify HMAC signature for webhook requests.
     */
    public function verifyHmacSignature(string $payload, string $signature): bool
    {
        if (!$this->hmac_mandatory || empty($this->hmac_token)) {
            return true;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $this->hmac_token);
        
        return hash_equals($expectedSignature, $signature);
    }
}
