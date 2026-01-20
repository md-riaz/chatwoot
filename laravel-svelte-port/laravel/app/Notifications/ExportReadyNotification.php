<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class ExportReadyNotification extends Notification
{
    use Queueable;

    public function __construct(protected string $path, protected int $accountId) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        // Provide a server-side download endpoint the frontend can call.
        // Route name `contacts.exports.download` is created in api routes.
        $downloadUrl = route('contacts.exports.download', ['account' => $this->accountId], true);

        return [
            'account_id' => $this->accountId,
            'notification_type' => 'contact_export_ready',
            'primary_actor_type' => null,
            'primary_actor_id' => null,
            'message' => 'Your contacts export is ready',
            'path' => $this->path,
            'download_url' => $downloadUrl,
            'meta' => [
                'message' => 'Your contacts export is ready',
                'download_url' => $downloadUrl,
            ],
        ];
    }
}
