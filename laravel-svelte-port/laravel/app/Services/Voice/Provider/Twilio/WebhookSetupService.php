<?php

namespace App\Services\Voice\Provider\Twilio;

use App\Models\Channels\Voice;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class WebhookSetupService
{
    private const HTTP_METHOD = 'POST';

    public function __construct(
        private Voice $channel
    ) {}

    /**
     * Setup Twilio webhooks and return TwiML App SID (Rails parity).
     */
    public function perform(): ?string
    {
        try {
            $this->validateTokenCredentials();
            $appSid = $this->createTwimlApp();
            $this->configureNumberWebhooks();
            
            return $appSid;
        } catch (\Exception $e) {
            $this->logTwilioError('WEBHOOK_SETUP_FAILED', $e);
            throw $e;
        }
    }

    private function validateTokenCredentials(): void
    {
        try {
            // Only validate Account SID + Auth Token
            $this->getTokenClient()->incomingPhoneNumbers->read(['limit' => 1]);
        } catch (\Exception $e) {
            $this->logTwilioError('AUTH_VALIDATION_TOKEN', $e);
            throw $e;
        }
    }

    private function createTwimlApp(): string
    {
        try {
            $friendlyName = "Chatwoot Voice {$this->channel->phone_number}";
            
            $app = $this->getApiKeyClient()->applications->create([
                'friendlyName' => $friendlyName,
                'voiceUrl' => $this->channel->voiceCallWebhookUrl(),
                'voiceMethod' => self::HTTP_METHOD,
            ]);
            
            return $app->sid;
        } catch (\Exception $e) {
            $this->logTwilioError('TWIML_APP_CREATE', $e);
            throw $e;
        }
    }

    private function configureNumberWebhooks(): void
    {
        try {
            $numbers = $this->getApiKeyClient()->incomingPhoneNumbers->read([
                'phoneNumber' => $this->channel->phone_number
            ]);

            if (empty($numbers)) {
                Log::warning("TWILIO_PHONE_NUMBER_NOT_FOUND: {$this->channel->phone_number}");
                return;
            }

            $this->getApiKeyClient()
                ->incomingPhoneNumbers($numbers[0]->sid)
                ->update([
                    'voiceUrl' => $this->channel->voiceCallWebhookUrl(),
                    'voiceMethod' => self::HTTP_METHOD,
                    'statusCallback' => $this->channel->voiceStatusWebhookUrl(),
                    'statusCallbackMethod' => self::HTTP_METHOD,
                ]);
        } catch (\Exception $e) {
            $this->logTwilioError('NUMBER_WEBHOOKS_UPDATE', $e);
            throw $e;
        }
    }

    private function getApiKeyClient(): Client
    {
        $config = $this->channel->provider_config;
        
        return new Client(
            $config['api_key_sid'],
            $config['api_key_secret'],
            $config['account_sid']
        );
    }

    private function getTokenClient(): Client
    {
        $config = $this->channel->provider_config;
        
        return new Client(
            $config['account_sid'],
            $config['auth_token']
        );
    }

    private function logTwilioError(string $context, \Exception $error): void
    {
        $config = $this->channel->provider_config;
        
        $details = [
            'context' => $context,
            'phone_number' => $this->channel->phone_number,
            'account_sid' => $config['account_sid'] ?? null,
            'error_class' => get_class($error),
            'message' => $error->getMessage(),
        ];

        // Add Twilio-specific error details if available
        if (method_exists($error, 'getStatusCode')) {
            $details['status_code'] = $error->getStatusCode();
        }
        if (method_exists($error, 'getCode')) {
            $details['twilio_code'] = $error->getCode();
        }
        if (method_exists($error, 'getMoreInfo')) {
            $details['more_info'] = $error->getMoreInfo();
        }
        if (method_exists($error, 'getDetails')) {
            $details['details'] = $error->getDetails();
        }

        $backtrace = array_slice($error->getTrace(), 0, 5);
        Log::error("TWILIO_VOICE_SETUP_ERROR: " . json_encode($details), ['backtrace' => $backtrace]);
    }
}