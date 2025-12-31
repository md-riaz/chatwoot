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

        $relayUrl = env('PUSH_RELAY_URL');

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
        $serverKey = env('FIREBASE_SERVER_KEY');

        if (! $serverKey || ! $pushToken) {
            Log::warning('FCM push skipped due to missing credentials or token', [
                'subscription_id' => $subscription->id,
                'user_id' => $this->userId,
            ]);

            return;
        }

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'to' => $pushToken,
            'notification' => [
                'title' => $this->title,
                'body' => $this->body,
            ],
            'data' => $this->data ?? [],
            'android' => [
                'priority' => 'high',
            ],
            'apns' => [
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                        'category' => (string) Str::uuid(),
                    ],
                ],
            ],
        ]);

        if ($response->failed()) {
            $error = $response->json('results.0.error') ?? $response->status();
            $this->handleSubscriptionFailure($subscription, $error);
        }
    }

    private function handleSubscriptionFailure(NotificationSubscription $subscription, $error): void
    {
        Log::warning('Push notification subscription failed', [
            'subscription_id' => $subscription->id,
            'user_id' => $this->userId,
            'error' => $error,
        ]);

        $subscription->delete();
    }
}
