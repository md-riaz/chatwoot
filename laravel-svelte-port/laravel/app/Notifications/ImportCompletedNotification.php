<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ImportCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(protected array $result, protected int $accountId) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'account_id' => $this->accountId,
            'notification_type' => 'contact_import_completed',
            'primary_actor_type' => null,
            'primary_actor_id' => null,
            'message' => 'Your contacts import has completed',
            'result' => $this->result,
            'meta' => [
                'message' => 'Your contacts import has completed',
                'result' => $this->result,
            ],
        ];
    }
}
