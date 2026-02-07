<?php

namespace App\Http\Controllers\Api\V2;

use App\Actions\Reports\GetAgentStatusMetricsAction;
use App\Http\Controllers\Api\V1\Concerns\RequiresAccountAdmin;
use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Agents Controller (V2)
 * 
 * Provides agent-related endpoints including status tracking.
 * Rails parity: Derived from OnlineStatusTracker usage patterns
 */
class AgentsController extends Controller
{
    use RequiresAccountAdmin;

    /**
     * Get agent status metrics (online, busy, offline counts).
     * 
     * GET /api/v2/accounts/{account}/agents/status
     */
    public function status(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $metrics = GetAgentStatusMetricsAction::run($account->id);
        
        return response()->json(['data' => $metrics]);
    }
}
