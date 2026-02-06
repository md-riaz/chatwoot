<?php

namespace App\Http\Controllers\Api\V2\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/**
 * Live Reports Controller
 * 
 * Provides real-time conversation metrics for dashboards and live monitoring.
 * Rails parity: app/controllers/api/v2/accounts/live_reports_controller.rb
 */
class LiveReportsController extends Controller
{
    /**
     * Get account-level conversation metrics
     * 
     * GET /api/v2/accounts/{account}/live_reports/conversation_metrics
     * 
     * @param Request $request
     * @param Account $account
     * @return JsonResponse
     */
    public function conversationMetrics(Request $request, Account $account): JsonResponse
    {
        // Authorization check
        Gate::authorize('view', ['report', $account]);

        // Load conversations with optional team filter
        $conversations = $this->loadConversations($account, $request->input('team_id'));

        // Calculate metrics matching Rails format exactly
        return response()->json([
            'open' => $conversations->open()->count(),
            'unattended' => $conversations->open()->unattended()->count(),
            'unassigned' => $conversations->open()->unassigned()->count(),
            'pending' => $conversations->pending()->count(),
        ]);
    }

    /**
     * Get grouped conversation metrics (by team or assignee)
     * 
     * GET /api/v2/accounts/{account}/live_reports/grouped_conversation_metrics
     * 
     * @param Request $request
     * @param Account $account
     * @return JsonResponse
     * @throws ValidationException
     */
    public function groupedConversationMetrics(Request $request, Account $account): JsonResponse
    {
        // Authorization check
        Gate::authorize('view', ['report', $account]);

        // Validate group_by parameter
        $groupBy = $request->input('group_by');
        if (!in_array($groupBy, ['team_id', 'assignee_id'])) {
            return response()->json(
                ['error' => 'invalid group_by'],
                422
            );
        }

        // Load conversations
        $conversations = $this->loadConversations($account, $request->input('team_id'));

        // Group metrics by the specified field
        $groupMetrics = $this->calculateGroupedMetrics($conversations, $groupBy);

        return response()->json($groupMetrics);
    }

    /**
     * Load conversations with optional team filter
     * 
     * @param Account $account
     * @param int|null $teamId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function loadConversations(Account $account, ?int $teamId = null)
    {
        $query = Conversation::where('account_id', $account->id);

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        return $query;
    }

    /**
     * Calculate grouped metrics matching Rails format
     * 
     * Rails implementation:
     * - Groups open conversations by the specified field
     * - Calculates open, unattended, and unassigned counts per group
     * - Returns array with group_id as a key in each metric object
     * 
     * @param \Illuminate\Database\Eloquent\Builder $conversations
     * @param string $groupBy
     * @return array
     */
    private function calculateGroupedMetrics($conversations, string $groupBy): array
    {
        // Get open conversations grouped by the field
        $openByGroup = $conversations->open()
            ->selectRaw("{$groupBy}, COUNT(*) as count")
            ->groupBy($groupBy)
            ->whereNotNull($groupBy)
            ->pluck('count', $groupBy)
            ->toArray();

        // Get unattended conversations grouped by the field
        $unattendedByGroup = $conversations->open()
            ->unattended()
            ->selectRaw("{$groupBy}, COUNT(*) as count")
            ->groupBy($groupBy)
            ->whereNotNull($groupBy)
            ->pluck('count', $groupBy)
            ->toArray();

        // Get unassigned conversations grouped by the field
        $unassignedByGroup = $conversations->open()
            ->unassigned()
            ->selectRaw("{$groupBy}, COUNT(*) as count")
            ->groupBy($groupBy)
            ->whereNotNull($groupBy)
            ->pluck('count', $groupBy)
            ->toArray();

        // Build metrics array matching Rails format exactly
        $metrics = [];
        foreach ($openByGroup as $groupId => $count) {
            $metric = [
                'open' => $count,
                'unattended' => $unattendedByGroup[$groupId] ?? 0,
                'unassigned' => $unassignedByGroup[$groupId] ?? 0,
                $groupBy => $groupId, // Add group_id as a key (Rails pattern)
            ];
            $metrics[] = $metric;
        }

        return $metrics;
    }
}
