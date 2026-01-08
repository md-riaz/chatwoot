<?php

namespace App\Jobs\Notification;

use App\Models\NotificationSetting;
use App\Models\NotificationSubscription;
use App\Models\User;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class SendPushNotificationJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 120;

    public string $queue = 'notifications';

    public function __construct(
        public int $userId,
        public string $title,
        public string $body,
        public ?array $data = [],
        public ?int $accountId = null,
        public ?string $notificationType = null
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            Log::warning('User not found for push notification', ['user_id' => $this->userId]);

            return;
        }

        if (! $this->shouldSendForNotificationSettings($user)) {
            Log::info('Skipping push notification due to user settings', [
                'user_id' => $user->id,
                'account_id' => $this->accountId,
                'notification_type' => $this->notificationType,
            ]);

            return;
        }

        $subscriptions = NotificationSubscription::where('user_id', $user->id)->get();

        foreach ($subscriptions as $subscription) {
            if ($subscription->subscription_type === 'fcm') {
                $this->sendFcmPush($subscription);
            } elseif ($subscription->subscription_type === 'browser_push') {
                $this->sendBrowserPush($subscription);
            } else {
                Log::debug('Unknown subscription type', [
                    'subscription_id' => $subscription->id,
                    'type' => $subscription->subscription_type,
                ]);
            }
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Push notification failed', [
            'user_id' => $this->userId,
            'title' => $this->title,
            'error' => $exception->getMessage(),
        ]);
    }

    private function shouldSendForNotificationSettings(User $user): bool
    {
        if (! $this->notificationType || ! $this->accountId) {
            return true;
        }

        $setting = NotificationSetting::where('user_id', $user->id)
            ->where('account_id', $this->accountId)
            ->first();

        if (! $setting) {
            return true;
        }

        $bit = NotificationSetting::NOTIFICATION_TYPES[$this->notificationType] ?? null;
        if ($bit === null) {
            return true;
        }

        return (bool) ($setting->push_flags & $bit);
    }

    private function sendBrowserPush(NotificationSubscription $subscription): void
    {
        $endpoint = data_get($subscription->subscription_attributes, 'endpoint');
        $payload = [
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
        ];

        $relayUrl = config('services.push_relay.url');

        if ($relayUrl && $endpoint) {
            $response = Http::timeout(5)->post($relayUrl, [
                'endpoint' => $endpoint,
                'payload' => $payload,
            ]);

            if ($response->failed()) {
                $this->handleSubscriptionFailure($subscription, $response->json('error') ?? $response->status());
            }
        } else {
            Log::info('Browser push relay not configured; skipping send', [
                'subscription_id' => $subscription->id,
                'user_id' => $this->userId,
            ]);
        }
    }

    private function sendFcmPush(NotificationSubscription $subscription): void
    {
        $pushToken = data_get($subscription->subscription_attributes, 'push_token');
        $projectId = config('services.firebase.project_id');

        if (! $projectId || ! $pushToken) {
            Log::warning('FCM push skipped due to missing credentials or token', [
                'subscription_id' => $subscription->id,
                'user_id' => $this->userId,
            ]);

            return;
        }

        $accessToken = $this->getFirebaseAccessToken();

        if (! $accessToken) {
            Log::warning('FCM push skipped due to missing access token', [
                'subscription_id' => $subscription->id,
                'user_id' => $this->userId,
            ]);

            return;
        }

        $payload = [
            'message' => [
                'token' => $pushToken,
                'notification' => [
                    'title' => $this->title,
                    'body' => $this->body,
                ],
                'data' => $this->data ?? [],
                'android' => [
                    'priority' => 'HIGH',
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'category' => (string) Str::uuid(),
                        ],
                    ],
                ],
            ],
        ];

        $response = Http::timeout(30)
            ->withToken($accessToken)
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $payload);

        if ($response->failed()) {
            $error = data_get($response->json(), 'error.message') ?? data_get($response->json(), 'error.status') ?? $response->status();
            $this->handleSubscriptionFailure($subscription, $error);
        }
    }

    private function handleSubscriptionFailure(NotificationSubscription $subscription, $error): void
    {
        $permanentErrors = ['NotRegistered', 'InvalidRegistration', 'MismatchSenderId'];

        Log::warning('Push notification subscription failed', [
            'subscription_id' => $subscription->id,
            'user_id' => $this->userId,
            'error' => $error,
        ]);

        if (in_array($error, $permanentErrors, true)) {
            Log::info('Removing invalid subscription', ['subscription_id' => $subscription->id]);
            $subscription->delete();
        }
    }

    private function getFirebaseAccessToken(): ?string
    {
        $credentials = config('services.firebase.credentials');

        if (! $credentials) {
            return null;
        }

        $cachedToken = Cache::get('firebase_messaging_access_token');
        if ($cachedToken) {
            return $cachedToken;
        }

        $credentialsArray = $this->normalizeFirebaseCredentials($credentials);

        $clientEmail = $credentialsArray['client_email'] ?? null;
        $privateKey = $credentialsArray['private_key'] ?? null;
        $tokenUri = $credentialsArray['token_uri'] ?? 'https://oauth2.googleapis.com/token';

        if (! $clientEmail || ! $privateKey) {
            return null;
        }

        $now = time();
        $payload = [
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => $tokenUri,
            'exp' => $now + 3600,
            'iat' => $now,
        ];

        $jwt = $this->encodeJwt($payload, $privateKey);

        if (! $jwt) {
            return null;
        }

        $response = Http::timeout(15)->asForm()->post($tokenUri, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if ($response->failed()) {
            return null;
        }

        $accessToken = $response->json('access_token');
        $expiresIn = (int) $response->json('expires_in', 3600);

        if ($accessToken) {
            Cache::put('firebase_messaging_access_token', $accessToken, now()->addSeconds(max($expiresIn - 60, 300)));
        }

        return $accessToken;
    }

    private function normalizeFirebaseCredentials(string|array $credentials): array
    {
        if (is_string($credentials) && file_exists($credentials)) {
            $credentials = file_get_contents($credentials) ?: '';
        }

        if (is_string($credentials)) {
            $decoded = json_decode($credentials, true);
        } else {
            $decoded = $credentials;
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function encodeJwt(array $payload, string $privateKey): ?string
    {
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $segments = [
            $this->base64UrlEncode(json_encode($header)),
            $this->base64UrlEncode(json_encode($payload)),
        ];

        $signingInput = implode('.', $segments);
        $signature = '';

        if (! openssl_sign($signingInput, $signature, $privateKey, 'sha256')) {
            return null;
        }

        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
