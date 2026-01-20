<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Models\Conversation;

class ConversationAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(public Conversation $conversation, public $previousAssignee = null)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $line = sprintf('You have been assigned conversation #%s', $this->conversation->display_id ?? $this->conversation->id);

        return (new MailMessage)
            ->subject('Conversation assigned')
            ->line($line)
            ->action('Open Conversation', url('/conversations/' . $this->conversation->id))
            ->line('Thanks for using our app!');
    }

    public function toArray($notifiable): array
    {
        return [
            'account_id' => $this->conversation->account_id,
            'notification_type' => \App\Models\NotificationSetting::NOTIFICATION_TYPES['conversation_assignment'],
            'primary_actor_type' => get_class($this->conversation),
            'primary_actor_id' => $this->conversation->id,
            'conversation_id' => $this->conversation->id,
            'previous_assignee_id' => $this->previousAssignee?->id ?? null,
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
