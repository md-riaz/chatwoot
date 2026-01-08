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
        return ['mail', 'database', 'broadcast'];
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
            'conversation_id' => $this->conversation->id,
            'previous_assignee_id' => $this->previousAssignee?->id ?? null,
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
