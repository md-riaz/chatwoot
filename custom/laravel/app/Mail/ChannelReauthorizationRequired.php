<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ChannelReauthorizationRequired extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private $channel,
        private string $channelType,
        private $user
    ) {}

    public function build()
    {
        $channelName = $this->getChannelDisplayName();
        
        return $this->subject("Action Required: {$channelName} Channel Needs Reauthorization")
                    ->view('emails.channel-reauthorization-required')
                    ->with([
                        'user' => $this->user,
                        'channel' => $this->channel,
                        'channelType' => $this->channelType,
                        'channelName' => $channelName,
                        'accountName' => $this->channel->account->name,
                    ]);
    }

    private function getChannelDisplayName(): string
    {
        return match ($this->channelType) {
            'Whatsapp' => 'WhatsApp',
            'FacebookPage' => 'Facebook Page',
            'Instagram' => 'Instagram',
            'Tiktok' => 'TikTok',
            default => $this->channelType,
        };
    }
}