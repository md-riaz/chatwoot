<?php

namespace App\Http\Controllers\Api\V2;

use App\Actions\Reports\GetLiveConversationMetricsAction;
use App\Actions\Reports\GetGroupedConversationMetricsAction;
use App\Http\Controllers\Api\V1\Concerns\RequiresAccountAdmin;
use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Live Reports Controller
 * 
 * Provides real-time conversation metrics for dashboards.
 * Rails parity: app/controllers/api/v2/accounts/live_reports_controller.rb
 */
class LiveReportsController extends Controller
{
    use RequiresAccountAdmin;

    /**
     * Get live conversation metrics.
     * 
     * GET /api/v2/accounts/{account}/live_reports/conversation_metrics
     */
    public function conversationMetrics(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $metrics = GetLiveConversationMetricsAction::run(
            $account->id,
            $request->input('team_id')
        );
        
        return response()->json($metrics);
    }

    /**
     * Get grouped conversation metrics.
     * 
     * GET /api/v2/accounts/{account}/live_reports/grouped_conversation_metrics
     */
    public function groupedConversationMetrics(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $groupBy = $request->input('group_by');
        
        // Validate group_by parameter
        if (!in_array($groupBy, ['team_id', 'assignee_id'])) {
            return response()->json(['error' => 'invalid group_by'], 422);
        }
        
        try {
            $metrics = GetGroupedConversationMetricsAction::run(
                $account->id,
                $groupBy,
                $request->input('team_id')
            );
            
            return response()->json($metrics);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}