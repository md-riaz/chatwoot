<?php

namespace App\Jobs\Notification;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public int $userId,
        public string $title,
        public string $body,
        public ?array $data = []
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (!$user) {
            Log::warning('User not found for push notification', ['user_id' => $this->userId]);
            return;
        }

        // Send push notification (placeholder - implement actual push service)
        // This could integrate with Firebase FCM, OneSignal, Pusher, etc.

        Log::info('Push notification sent', [
            'user_id' => $this->userId,
            'title' => $this->title,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Push notification failed', [
            'user_id' => $this->userId,
            'title' => $this->title,
            'error' => $exception->getMessage(),
        ]);
    }
}
