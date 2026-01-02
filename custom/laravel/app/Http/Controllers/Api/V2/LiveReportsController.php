<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\Concerns\RequiresAccountAdmin;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Conversation;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveReportsController extends Controller
{
    use RequiresAccountAdmin;

    /**
     * Get live conversation metrics.
     */
    public function conversationMetrics(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $conversations = $this->loadConversations($account, $request);
        
        $metrics = [
            'open' => $conversations->where('status', 'open')->count(),
            'unattended' => $conversations->where('status', 'open')
                ->whereNull('last_activity_at')
                ->count(),
            'unassigned' => $conversations->where('status', 'open')
                ->whereNull('assignee_id')
                ->count(),
            'pending' => $conversations->where('status', 'pending')->count(),
        ];
        
        return response()->json($metrics);
    }

    /**
     * Get grouped conversation metrics.
     */
    public function groupedConversationMetrics(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $groupBy = $this->validateGroupBy($request);
        if (!$groupBy) {
            return response()->json(['error' => 'invalid group_by'], 422);
        }
        
        $conversations = $this->loadConversations($account, $request);
        
        // Group conversations by the specified field
        $grouped = $conversations->groupBy($groupBy);
        
        $groupMetrics = [];
        
        foreach ($grouped as $groupId => $groupConversations) {
            $openConversations = $groupConversations->where('status', 'open');
            
            $metric = [
                'open' => $openConversations->count(),
                'unattended' => $openConversations->whereNull('last_activity_at')->count(),
                'unassigned' => $openConversations->whereNull('assignee_id')->count(),
                $groupBy => $groupId,
            ];
            
            $groupMetrics[] = $metric;
        }
        
        return response()->json($groupMetrics);
    }

    /**
     * Load conversations based on request parameters.
     */
    private function loadConversations(Account $account, Request $request)
    {
        $query = $account->conversations();
        
        // Filter by team if specified
        if ($request->has('team_id')) {
            $team = $account->teams()->find($request->get('team_id'));
            if ($team) {
                $query->where('team_id', $team->id);
            }
        }
        
        return $query->get();
    }

    /**
     * Validate and return the group_by parameter.
     */
    private function validateGroupBy(Request $request): ?string
    {
        $groupBy = $request->get('group_by');
        
        $allowedGroupBy = ['team_id', 'assignee_id'];
        
        if (!in_array($groupBy, $allowedGroupBy)) {
            return null;
        }
        
        return $groupBy;
    }
}