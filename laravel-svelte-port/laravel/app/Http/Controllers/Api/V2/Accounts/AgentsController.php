<?php

namespace App\Http\Controllers\Api\V2\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\OnlineStatusTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

/**
 * Agents Controller
 * 
 * Provides agent-related endpoints including status tracking.
 * Rails parity: Derived from OnlineStatusTracker usage patterns
 */
class AgentsController extends Controller
{
    /**
     * Get agent status metrics (online, busy, offline counts)
     * 
     * GET /api/v2/accounts/{account}/agents/status
     * 
     * @param Account $account
     * @return JsonResponse
     */
    public function status(Account $account): JsonResponse
    {
        // Authorization check
        Gate::authorize('view', ['report', $account]);

        // Get available users with their statuses from Redis
        $availableUsers = OnlineStatusTracker::getAvailableUsers($account->id);

        // Count users by status
        $online = 0;
        $busy = 0;
        $offline = 0;

        foreach ($availableUsers as $userId => $status) {
            switch ($status) {
                case 'online':
                    $online++;
                    break;
                case 'busy':
                    $busy++;
                    break;
                case 'offline':
                    $offline++;
                    break;
            }
        }

        // Get total agents count to calculate offline
        $totalAgents = $account->accountUsers()->count();
        $offline = $totalAgents - $online - $busy;

        // Return metrics matching Rails format
        return response()->json([
            'online' => $online,
            'busy' => $busy,
            'offline' => max(0, $offline), // Ensure non-negative
        ]);
    }
}
