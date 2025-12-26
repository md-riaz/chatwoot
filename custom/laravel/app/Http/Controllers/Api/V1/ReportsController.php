<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    /**
     * Get account reports summary.
     */
    public function index(Account $account, Request $request): JsonResponse
    {
        $since = $request->get('since', now()->subDays(7)->toDateString());
        $until = $request->get('until', now()->toDateString());
        $type = $request->get('type', 'account');

        $metrics = $this->getMetrics($account, $since, $until);

        return response()->json(['data' => $metrics]);
    }

    /**
     * Get conversation metrics.
     */
    public function conversations(Account $account, Request $request): JsonResponse
    {
        $since = $request->get('since', now()->subDays(7)->toDateString());
        $until = $request->get('until', now()->toDateString());

        $metrics = DB::table('conversations')
            ->where('account_id', $account->id)
            ->whereBetween('created_at', [$since, $until])
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(CASE WHEN status = "open" THEN 1 ELSE 0 END) as open_count,
                SUM(CASE WHEN status = "resolved" THEN 1 ELSE 0 END) as resolved_count,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_count
            ')
            ->first();

        return response()->json(['data' => $metrics]);
    }

    /**
     * Get agent metrics.
     */
    public function agents(Account $account, Request $request): JsonResponse
    {
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
                SUM(CASE WHEN conversations.status = "resolved" THEN 1 ELSE 0 END) as resolved_count
            ')
            ->get();

        return response()->json(['data' => $agents]);
    }

    /**
     * Get inbox metrics.
     */
    public function inboxes(Account $account, Request $request): JsonResponse
    {
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
                SUM(CASE WHEN conversations.status = "resolved" THEN 1 ELSE 0 END) as resolved_count
            ')
            ->get();

        return response()->json(['data' => $inboxes]);
    }

    /**
     * Get team metrics.
     */
    public function teams(Account $account, Request $request): JsonResponse
    {
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
                SUM(CASE WHEN conversations.status = "resolved" THEN 1 ELSE 0 END) as resolved_count
            ')
            ->get();

        return response()->json(['data' => $teams]);
    }

    /**
     * Get label metrics.
     */
    public function labels(Account $account, Request $request): JsonResponse
    {
        $since = $request->get('since', now()->subDays(7)->toDateString());
        $until = $request->get('until', now()->toDateString());

        // This would need to be implemented based on how labels are stored
        $labels = collect();

        return response()->json(['data' => $labels]);
    }

    /**
     * Download report as CSV.
     */
    public function download(Account $account, Request $request): JsonResponse
    {
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
            'resolutions_count' => (clone $conversations)->where('status', 'resolved')->count(),
        ];
    }
}
