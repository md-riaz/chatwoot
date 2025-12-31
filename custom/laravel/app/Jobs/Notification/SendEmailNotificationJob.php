<?php

namespace App\Jobs\Notification;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendEmailNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 120;

    public string $queue = 'notifications';

    public function __construct(
        public int $userId,
        public string $subject,
        public string $content,
        public ?array $data = []
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            Log::warning('User not found for email notification', ['user_id' => $this->userId]);

            return;
        }

        // Send email (using Laravel's mail facade)
        // This is a placeholder - implement actual mailable class
        Mail::raw($this->content, function ($message) use ($user) {
            $message->to($user->email)
                ->subject($this->subject);
        });

        Log::info('Email notification sent', [
            'user_id' => $this->userId,
            'subject' => $this->subject,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Email notification failed', [
            'user_id' => $this->userId,
            'subject' => $this->subject,
            'error' => $exception->getMessage(),
        ]);
    }
}
