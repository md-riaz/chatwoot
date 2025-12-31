<?php

namespace App\Jobs\Campaigns;

use App\Actions\Message\CreateMessageAction;
use App\Actions\Reporting\IngestReportingEventAction;
use App\Data\Message\MessageData;
use App\Jobs\SendReplyJob;
use App\Models\Campaign;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCampaignMessagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 120;

    public int $timeout = 300;

    public string $queue = 'campaigns';

    public function __construct(public int $campaignId) {}

    public function handle(): void
    {
        $campaign = Campaign::with(['conversations.inbox', 'conversations.account'])->find($this->campaignId);

        if (! $campaign || ! $campaign->enabled || ! $campaign->isActive()) {
            return;
        }

        if ($campaign->conversations->isEmpty()) {
            Log::warning('Campaign has no target conversations', ['campaign_id' => $campaign->id]);
            return;
        }

        foreach ($campaign->conversations as $conversation) {
            $messageData = new MessageData(
                id: null,
                account_id: $conversation->account_id,
                conversation_id: $conversation->id,
                inbox_id: $conversation->inbox_id,
                sender_id: $campaign->sender_id,
                sender_type: \App\Models\User::class,
                message_type: Message::TYPE_OUTGOING,
                content: $campaign->message,
                content_type: Message::CONTENT_TEXT,
                content_attributes: [
                    'campaign_id' => $campaign->id,
                    'template_params' => $campaign->template_params,
                ],
                private: false,
                external_source_id: null
            );

            $message = CreateMessageAction::run($messageData);
            SendReplyJob::dispatch($message->id)->onQueue('deliveries');

            IngestReportingEventAction::run([
                'account_id' => $conversation->account_id,
                'conversation_id' => $conversation->id,
                'inbox_id' => $conversation->inbox_id,
                'name' => 'campaign_message_dispatched',
                'value' => 1,
                'event_start_time' => now(),
            ]);
        }

        $campaign->update(['campaign_status' => Campaign::STATUS_COMPLETED]);

        Log::info('Campaign messages dispatched', ['campaign_id' => $campaign->id]);
    }
}
