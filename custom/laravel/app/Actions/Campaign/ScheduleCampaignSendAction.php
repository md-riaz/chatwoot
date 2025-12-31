<?php

namespace App\Actions\Campaign;

use App\Jobs\Campaigns\SendCampaignMessagesJob;
use App\Models\Campaign;
use Lorisleiva\Actions\Concerns\AsAction;

class ScheduleCampaignSendAction
{
    use AsAction;

    public function handle(Campaign $campaign): void
    {
        if (! $campaign->enabled || ! $campaign->isActive()) {
            return;
        }

        $dispatchAt = $campaign->scheduled_at && $campaign->scheduled_at->isFuture()
            ? $campaign->scheduled_at
            : now();

        SendCampaignMessagesJob::dispatch($campaign->id)
            ->delay($dispatchAt)
            ->onQueue('campaigns');
    }
}
