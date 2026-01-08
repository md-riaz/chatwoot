<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\RequiresAccountAdmin;
use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    use RequiresAccountAdmin;
    /**
     * Get account reports summary.
     * Requires admin role.
     */
    public function index(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $since = $request->get('since', now()->subDays(7)->toDateString());
        $until = $request->get('until', now()->toDateString());
        $type = $request->get('type', 'account');

        $metrics = $this->getMetrics($account, $since, $until);

        return response()->json(['data' => $metrics]);
    }

    /**
     * Get conversation metrics.
     * Requires admin role.
     */
    public function conversations(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $since = $request->get('since', now()->subDays(7)->toDateString());
        $until = $request->get('until', now()->toDateString());

        $metrics = DB::table('conversations')
            ->where('account_id', $account->id)
            ->whereBetween('created_at', [$since, $until])
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as open_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as resolved_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_count
            ', [
                \App\Models\Conversation::STATUS_OPEN,
                \App\Models\Conversation::STATUS_RESOLVED,
                \App\Models\Conversation::STATUS_PENDING
            ])
            ->first();

        return response()->json(['data' => $metrics]);
    }

    /**
     * Get agent metrics.
     * Requires admin role.
     */
    public function agents(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $since = $request->get('since', now()->subDays(7)->toDateString());
        $until = $request->get('until', now()->toDateString());

        $agents = DB::table('conversations')
            ->join('users', 'conversations.assignee_id', '=', 'users.id')
            ->where('conversations.account_id', $account->id)
            ->whereBetween('conversations.created_at', [$since, $until])
            ->groupBy('users.id', 'users.name', 'users.email')
            ->selectRaw('
                users.id,
                users.name,
                users.email,
                COUNT(*) as conversations_count,
                SUM(CASE WHEN conversations.status = ? THEN 1 ELSE 0 END) as resolved_count
            ', [\App\Models\Conversation::STATUS_RESOLVED])
            ->get();

        return response()->json(['data' => $agents]);
    }

    /**
     * Get inbox metrics.
     * Requires admin role.
     */
    public function inboxes(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $since = $request->get('since', now()->subDays(7)->toDateString());
        $until = $request->get('until', now()->toDateString());

        $inboxes = DB::table('conversations')
            ->join('inboxes', 'conversations.inbox_id', '=', 'inboxes.id')
            ->where('conversations.account_id', $account->id)
            ->whereBetween('conversations.created_at', [$since, $until])
            ->groupBy('inboxes.id', 'inboxes.name')
            ->selectRaw('
                inboxes.id,
                inboxes.name,
                COUNT(*) as conversations_count,
                SUM(CASE WHEN conversations.status = ? THEN 1 ELSE 0 END) as resolved_count
            ', [\App\Models\Conversation::STATUS_RESOLVED])
            ->get();

        return response()->json(['data' => $inboxes]);
    }

    /**
     * Get team metrics.
     * Requires admin role.
     */
    public function teams(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $since = $request->get('since', now()->subDays(7)->toDateString());
        $until = $request->get('until', now()->toDateString());

        $teams = DB::table('conversations')
            ->join('teams', 'conversations.team_id', '=', 'teams.id')
            ->where('conversations.account_id', $account->id)
            ->whereBetween('conversations.created_at', [$since, $until])
            ->groupBy('teams.id', 'teams.name')
            ->selectRaw('
                teams.id,
                teams.name,
                COUNT(*) as conversations_count,
                SUM(CASE WHEN conversations.status = ? THEN 1 ELSE 0 END) as resolved_count
            ', [\App\Models\Conversation::STATUS_RESOLVED])
            ->get();

        return response()->json(['data' => $teams]);
    }

    /**
     * Get label metrics.
     * Requires admin role.
     */
    public function labels(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $since = $request->get('since', now()->subDays(7)->toDateString());
        $until = $request->get('until', now()->toDateString());

        // This would need to be implemented based on how labels are stored
        $labels = collect();

        return response()->json(['data' => $labels]);
    }

    /**
     * Download report as CSV.
     * Requires admin role.
     */
    public function download(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $type = $request->get('type', 'conversations');
        $since = $request->get('since', now()->subDays(7)->toDateString());
        $until = $request->get('until', now()->toDateString());

        // In production, this would generate and return a CSV file
        return response()->json([
            'message' => 'Report download initiated',
            'type' => $type,
            'since' => $since,
            'until' => $until,
        ]);
    }

    /**
     * Get metrics for account.
     */
    private function getMetrics(Account $account, string $since, string $until): array
    {
        $conversations = DB::table('conversations')
            ->where('account_id', $account->id)
            ->whereBetween('created_at', [$since, $until]);

        return [
            'conversations_count' => $conversations->count(),
            'incoming_count' => (clone $conversations)->where('messages.message_type', 0)->count(),
            'outgoing_count' => (clone $conversations)->where('messages.message_type', 1)->count(),
            'resolutions_count' => (clone $conversations)->where('status', \App\Models\Conversation::STATUS_RESOLVED)->count(),
        ];
    }
}
