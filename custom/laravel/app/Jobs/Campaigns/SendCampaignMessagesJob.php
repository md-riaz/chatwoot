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
use Throwable;

class SendCampaignMessagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 120;

    public int $timeout = 300;

    public string $queue = 'campaigns';

    private const CHUNK_SIZE = 100;

    public function __construct(public int $campaignId) {}

    public function handle(): void
    {
        $campaign = Campaign::find($this->campaignId);

        if (! $campaign || ! $campaign->enabled || ! $campaign->isActive()) {
            return;
        }

        $conversationQuery = $campaign->conversations()->with(['inbox', 'account']);

        if (! $conversationQuery->exists()) {
            Log::warning('Campaign has no target conversations', ['campaign_id' => $campaign->id]);

            return;
        }

        $successCount = 0;
        $failureCount = 0;

        $conversationQuery->chunkById(self::CHUNK_SIZE, function ($conversations) use ($campaign, &$successCount, &$failureCount) {
            foreach ($conversations as $conversation) {
                try {
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

                    $successCount++;
                } catch (Throwable $e) {
                    $failureCount++;
                    Log::error('Failed to process campaign conversation', [
                        'campaign_id' => $campaign->id,
                        'conversation_id' => $conversation->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        $campaignStatus = $failureCount === 0
            ? Campaign::STATUS_COMPLETED
            : ($successCount > 0 ? Campaign::STATUS_PARTIALLY_COMPLETED : Campaign::STATUS_FAILED);

        $campaign->update([
            'campaign_status' => $campaignStatus,
        ]);

        Log::info('Campaign messages dispatched', [
            'campaign_id' => $campaign->id,
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'status' => $campaignStatus,
        ]);
    }
}
