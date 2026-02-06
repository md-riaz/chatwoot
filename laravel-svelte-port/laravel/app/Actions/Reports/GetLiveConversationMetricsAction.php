<?php

namespace App\Actions\Reports;

use App\Repositories\Reports\LiveReportsRepository;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Get Live Conversation Metrics Action
 * 
 * Returns real-time conversation metrics for an account.
 * Rails parity: Api::V2::Accounts::LiveReportsController#conversation_metrics
 */
class GetLiveConversationMetricsAction
{
    use AsAction;

    public function handle(int $accountId, ?int $teamId = null): array
    {
        $repository = app(LiveReportsRepository::class);
        
        return $repository->getAccountMetrics($accountId, $teamId);
    }
}
