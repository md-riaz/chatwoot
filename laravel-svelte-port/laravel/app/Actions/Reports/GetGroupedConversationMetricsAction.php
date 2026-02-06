<?php

namespace App\Actions\Reports;

use App\Repositories\Reports\LiveReportsRepository;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Get Grouped Conversation Metrics Action
 * 
 * Returns conversation metrics grouped by team_id or assignee_id.
 * Rails parity: Api::V2::Accounts::LiveReportsController#grouped_conversation_metrics
 */
class GetGroupedConversationMetricsAction
{
    use AsAction;

    public function handle(int $accountId, string $groupBy, ?int $teamId = null): array
    {
        // Validate group_by parameter
        if (!in_array($groupBy, ['team_id', 'assignee_id'])) {
            throw new \InvalidArgumentException('Invalid group_by parameter. Must be team_id or assignee_id.');
        }

        $repository = app(LiveReportsRepository::class);
        
        return $repository->getGroupedMetrics($accountId, $groupBy, $teamId);
    }
}
