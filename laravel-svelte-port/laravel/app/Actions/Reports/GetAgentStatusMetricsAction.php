<?php

namespace App\Actions\Reports;

use App\Models\Account;
use App\Services\OnlineStatusTracker;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Get Agent Status Metrics Action
 * 
 * Returns count of agents by status (online, busy, offline).
 * Rails parity: Derived from OnlineStatusTracker usage
 */
class GetAgentStatusMetricsAction
{
    use AsAction;

    public function handle(int $accountId): array
    {
        // Get available users with their statuses from Redis
        $availableUsers = OnlineStatusTracker::getAvailableUsers($accountId);

        // Count users by status
        $online = 0;
        $busy = 0;
        $offline = 0;

        foreach ($availableUsers as $userId => $status) {
            match ($status) {
                'online' => $online++,
                'busy' => $busy++,
                'offline' => $offline++,
                default => null,
            };
        }

        // Get total agents count to calculate offline
        $account = Account::find($accountId);
        $totalAgents = $account->accountUsers()->count();
        $offline = $totalAgents - $online - $busy;

        return [
            'online' => $online,
            'busy' => $busy,
            'offline' => max(0, $offline), // Ensure non-negative
        ];
    }
}
