<?php

namespace App\Models\Channels;

use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Sms extends Model
{
    use HasFactory;

    protected $table = 'channel_sms';

    // Provider constants
    public const PROVIDER_BANDWIDTH = 'bandwidth';
    public const PROVIDER_DEFAULT = 'default';

    protected $fillable = [
        'account_id',
        'phone_number',
        'provider',
        'provider_config',
    ];

    protected $casts = [
        'provider_config' => 'array',
    ];

    protected $attributes = [
        'provider' => self::PROVIDER_DEFAULT,
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function inbox(): MorphOne
    {
        return $this->morphOne(Inbox::class, 'channel');
    }

    public function getName(): string
    {
        return 'SMS';
    }

    /**
     * Get the API base path for the provider.
     */
    public function getApiBasePath(): string
    {
        return match ($this->provider) {
            self::PROVIDER_BANDWIDTH => 'https://messaging.bandwidth.com/api/v2',
            default => 'https://messaging.bandwidth.com/api/v2', // Default to Bandwidth for now
        };
    }

    /**
     * Send a message with optional attachments.
     */
    public function sendMessage(string $contactNumber, $message): ?string
    {
        $body = $this->buildMessageBody($contactNumber, $message->outgoing_content ?? $message);
        
        // Add media attachments if present
        if (isset($message->attachments) && $message->attachments->isNotEmpty()) {
            $body['media'] = $message->attachments->map(fn($attachment) => $attachment->download_url)->toArray();
        }

        return $this->sendToBandwidth($body, $message);
    }

    /**
     * Send a simple text message.
     */
    public function sendTextMessage(string $contactNumber, string $messageContent): ?string
    {
        $body = $this->buildMessageBody($contactNumber, $messageContent);
        return $this->sendToBandwidth($body);
    }

    /**
     * Build the message body for the API request.
     */
    protected function buildMessageBody(string $contactNumber, string $messageContent): array
    {
        return [
            'to' => $contactNumber,
            'from' => $this->phone_number,
            'text' => $messageContent,
            'applicationId' => $this->provider_config['application_id'] ?? null,
        ];
    }

    /**
     * Send message to Bandwidth API.
     */
    protected function sendToBandwidth(array $body, $message = null): ?string
    {
        try {
            $response = Http::withBasicAuth(
                $this->provider_config['api_key'] ?? '',
                $this->provider_config['api_secret'] ?? ''
            )->withHeaders([
                'Content-Type' => 'application/json',
            ])->post(
                $this->getApiBasePath() . "/users/{$this->provider_config['account_id']}/messages",
                $body
            );

            if ($response->successful()) {
                $responseData = $response->json();
                
                Log::info('SMS sent successfully', [
                    'channel_id' => $this->id,
                    'message_id' => $responseData['id'] ?? null,
                    'to' => $body['to'],
                ]);

                return $responseData['id'] ?? null;
            } else {
                $this->handleError($response, $message);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('SMS send failed', [
                'channel_id' => $this->id,
                'error' => $e->getMessage(),
                'to' => $body['to'],
            ]);

            if ($message && method_exists($message, 'update')) {
                $message->update([
                    'external_error' => $e->getMessage(),
                    'status' => 'failed',
                ]);
            }

            return null;
        }
    }

    /**
     * Handle API errors.
     */
    protected function handleError($response, $message = null): void
    {
        $errorData = $response->json();
        $errorMessage = $errorData['description'] ?? 'Unknown SMS error';

        Log::error("SMS Error for account {$this->account_id}", [
            'error' => $errorMessage,
            'response_status' => $response->status(),
            'channel_id' => $this->id,
        ]);

        if ($message && method_exists($message, 'update')) {
            $message->update([
                'external_error' => $errorMessage,
                'status' => 'failed',
            ]);
        }
    }

    /**
     * Validate provider configuration.
     */
    public function validateProviderConfig(): bool
    {
        try {
            $response = Http::withBasicAuth(
                $this->provider_config['api_key'] ?? '',
                $this->provider_config['api_secret'] ?? ''
            )->withHeaders([
                'Content-Type' => 'application/json',
            ])->post(
                $this->getApiBasePath() . "/users/{$this->provider_config['account_id']}/messages",
                [] // Empty body for validation
            );

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('SMS provider config validation failed', [
                'channel_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Get required provider config fields.
     */
    public function getRequiredConfigFields(): array
    {
        return match ($this->provider) {
            self::PROVIDER_BANDWIDTH => [
                'account_id',
                'api_key',
                'api_secret',
                'application_id',
            ],
            default => [
                'account_id',
                'api_key',
                'api_secret',
                'application_id',
            ],
        };
    }

    /**
     * Check if provider config is complete.
     */
    public function hasCompleteConfig(): bool
    {
        $requiredFields = $this->getRequiredConfigFields();
        $config = $this->provider_config ?? [];

        foreach ($requiredFields as $field) {
            if (empty($config[$field])) {
                return false;
            }
        }

        return true;
    }
}
