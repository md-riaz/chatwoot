<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\ChannelReauthorizationRequired;

class SendReauthorizationNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private $channel,
        private string $channelType
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        try {
            $account = $this->channel->account;
            $adminUsers = $account->users()->where('role', 1)->get(); // 1 = administrator

            foreach ($adminUsers as $admin) {
                Mail::to($admin->email)->send(
                    new ChannelReauthorizationRequired($this->channel, $this->channelType, $admin)
                );
            }

        } catch (\Exception $e) {
            \Log::error('Failed to send reauthorization notification', [
                'channel_id' => $this->channel->id,
                'channel_type' => $this->channelType,
                'error' => $e->getMessage()
            ]);
        }
    }
}