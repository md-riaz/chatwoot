<?php

namespace App\Services\Channels\Sms;

use App\Models\Inbox;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;

class TwilioService
{
    protected ?Client $client = null;
    protected ?string $accountSid;
    protected ?string $authToken;
    protected ?string $phoneNumber;

    public function __construct(?Inbox $inbox = null)
    {
        if ($inbox && $inbox->channel) {
            $this->accountSid = $inbox->channel->account_sid ?? null;
            $this->authToken = $inbox->channel->auth_token ?? null;
            $this->phoneNumber = $inbox->channel->phone_number ?? null;
        } else {
            $this->accountSid = config('services.twilio.sid');
            $this->authToken = config('services.twilio.token');
            $this->phoneNumber = config('services.twilio.phone');
        }
    }

    /**
     * Get Twilio client
     */
    protected function getClient(): ?Client
    {
        if ($this->client) {
            return $this->client;
        }

        if (!$this->accountSid || !$this->authToken) {
            return null;
        }

        try {
            $this->client = new Client($this->accountSid, $this->authToken);
            return $this->client;
        } catch (\Exception $e) {
            Log::error('Twilio client initialization failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Send SMS message
     */
    public function sendSms(string $to, string $message, ?string $from = null): array
    {
        $client = $this->getClient();
        if (!$client) {
            return ['success' => false, 'error' => 'Twilio client not configured'];
        }

        try {
            $result = $client->messages->create($to, [
                'from' => $from ?? $this->phoneNumber,
                'body' => $message,
            ]);

            return [
                'success' => true,
                'sid' => $result->sid,
                'status' => $result->status,
                'to' => $result->to,
                'from' => $result->from,
            ];
        } catch (TwilioException $e) {
            Log::error('Twilio send SMS failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send MMS message
     */
    public function sendMms(string $to, string $message, array $mediaUrls, ?string $from = null): array
    {
        $client = $this->getClient();
        if (!$client) {
            return ['success' => false, 'error' => 'Twilio client not configured'];
        }

        try {
            $result = $client->messages->create($to, [
                'from' => $from ?? $this->phoneNumber,
                'body' => $message,
                'mediaUrl' => $mediaUrls,
            ]);

            return [
                'success' => true,
                'sid' => $result->sid,
                'status' => $result->status,
                'to' => $result->to,
                'from' => $result->from,
            ];
        } catch (TwilioException $e) {
            Log::error('Twilio send MMS failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get available phone numbers
     */
    public function getAvailableNumbers(string $countryCode = 'US', array $options = []): array
    {
        $client = $this->getClient();
        if (!$client) {
            return [];
        }

        try {
            $params = array_merge([
                'smsEnabled' => true,
            ], $options);

            $numbers = $client->availablePhoneNumbers($countryCode)->local->read($params, 20);

            return array_map(function ($number) {
                return [
                    'phone_number' => $number->phoneNumber,
                    'friendly_name' => $number->friendlyName,
                    'locality' => $number->locality,
                    'region' => $number->region,
                    'postal_code' => $number->postalCode,
                    'capabilities' => [
                        'sms' => $number->capabilities['sms'] ?? false,
                        'mms' => $number->capabilities['mms'] ?? false,
                        'voice' => $number->capabilities['voice'] ?? false,
                    ],
                ];
            }, $numbers);
        } catch (TwilioException $e) {
            Log::error('Twilio get available numbers failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Purchase phone number
     */
    public function purchaseNumber(string $phoneNumber): array
    {
        $client = $this->getClient();
        if (!$client) {
            return ['success' => false, 'error' => 'Twilio client not configured'];
        }

        try {
            $result = $client->incomingPhoneNumbers->create([
                'phoneNumber' => $phoneNumber,
            ]);

            return [
                'success' => true,
                'sid' => $result->sid,
                'phone_number' => $result->phoneNumber,
                'friendly_name' => $result->friendlyName,
            ];
        } catch (TwilioException $e) {
            Log::error('Twilio purchase number failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get message status
     */
    public function getMessageStatus(string $messageSid): ?array
    {
        $client = $this->getClient();
        if (!$client) {
            return null;
        }

        try {
            $message = $client->messages($messageSid)->fetch();

            return [
                'sid' => $message->sid,
                'status' => $message->status,
                'to' => $message->to,
                'from' => $message->from,
                'body' => $message->body,
                'date_sent' => $message->dateSent,
                'error_code' => $message->errorCode,
                'error_message' => $message->errorMessage,
            ];
        } catch (TwilioException $e) {
            Log::error('Twilio get message status failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Process incoming webhook
     */
    public function processWebhook(array $payload): array
    {
        return [
            'message_sid' => $payload['MessageSid'] ?? null,
            'account_sid' => $payload['AccountSid'] ?? null,
            'from' => $payload['From'] ?? null,
            'to' => $payload['To'] ?? null,
            'body' => $payload['Body'] ?? null,
            'num_media' => (int) ($payload['NumMedia'] ?? 0),
            'media' => $this->extractMedia($payload),
            'from_city' => $payload['FromCity'] ?? null,
            'from_state' => $payload['FromState'] ?? null,
            'from_country' => $payload['FromCountry'] ?? null,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Extract media from webhook payload
     */
    protected function extractMedia(array $payload): array
    {
        $media = [];
        $numMedia = (int) ($payload['NumMedia'] ?? 0);

        for ($i = 0; $i < $numMedia; $i++) {
            if (isset($payload["MediaUrl{$i}"])) {
                $media[] = [
                    'url' => $payload["MediaUrl{$i}"],
                    'content_type' => $payload["MediaContentType{$i}"] ?? null,
                ];
            }
        }

        return $media;
    }

    /**
     * Validate webhook signature
     */
    public function validateWebhook(string $signature, string $url, array $params): bool
    {
        try {
            $validator = new \Twilio\Security\RequestValidator($this->authToken);
            return $validator->validate($signature, $url, $params);
        } catch (\Exception $e) {
            Log::error('Twilio webhook validation failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
