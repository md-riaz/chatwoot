<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class AuditLogsController extends Controller
{
    /**
     * Display a listing of audit logs for an account.
     */
    public function index(Account $account, Request $request): JsonResource
    {
        // Only admins (role >= 2) can view audit logs
        $user = $request->user();
        $accountUser = $account->users()->where('user_id', $user->id)->first();
        if (! $accountUser || $accountUser->pivot->role < 2) {
            abort(403, 'Only admins can view audit logs');
        }

        $query = DB::table('audit_logs')
            ->where('account_id', $account->id)
            ->orderBy('created_at', 'desc');

        // Filter by auditable type
        if ($request->has('auditable_type')) {
            $query->where('auditable_type', $request->auditable_type);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filter by date range
        if ($request->has('since')) {
            $query->where('created_at', '>=', $request->since);
        }

        if ($request->has('until')) {
            $query->where('created_at', '<=', $request->until);
        }

        return JsonResource::collection($query->paginate($request->get('per_page', 25)));
    }

    /**
     * Display the specified audit log.
     */
    public function show(Account $account, int $logId, Request $request): JsonResponse
    {
        // Only admins can view audit logs
        $user = $request->user();
        $accountUser = $account->users()->where('user_id', $user->id)->first();
        if (! $accountUser || $accountUser->pivot->role < 2) {
            abort(403, 'Only admins can view audit logs');
        }

        $log = DB::table('audit_logs')
            ->where('account_id', $account->id)
            ->where('id', $logId)
            ->first();

        abort_unless($log, 404);

        return response()->json(['data' => $log]);
    }

    /**
     * Get audit log summary/statistics.
     */
    public function summary(Account $account, Request $request): JsonResponse
    {
        // Only admins can view audit logs
        $user = $request->user();
        $accountUser = $account->users()->where('user_id', $user->id)->first();
        if (! $accountUser || $accountUser->pivot->role < 2) {
            abort(403, 'Only admins can view audit logs');
        }

        $since = $request->get('since', now()->subDays(7)->toDateString());
        $until = $request->get('until', now()->toDateString());

        $summary = DB::table('audit_logs')
            ->where('account_id', $account->id)
            ->whereBetween('created_at', [$since, $until])
            ->selectRaw('
                action,
                auditable_type,
                COUNT(*) as count
            ')
            ->groupBy('action', 'auditable_type')
            ->get();

        return response()->json(['data' => $summary]);
    }

    /**
     * Download audit logs as CSV.
     */
    public function download(Account $account, Request $request): JsonResponse
    {
        // Only admins can export audit logs
        $user = $request->user();
        $accountUser = $account->users()->where('user_id', $user->id)->first();
        if (! $accountUser || $accountUser->pivot->role < 2) {
            abort(403, 'Only admins can export audit logs');
        }

        $since = $request->get('since', now()->subDays(7)->toDateString());
        $until = $request->get('until', now()->toDateString());

        $logs = DB::table('audit_logs')
            ->where('account_id', $account->id)
            ->whereBetween('created_at', [$since, $until])
            ->get();

        // In production, this would generate and return a CSV file
        return response()->json([
            'message' => 'Audit log download initiated',
            'count' => $logs->count(),
        ]);
    }

    /**
     * Get audit logs for a specific resource.
     */
    public function forResource(Account $account, string $type, int $id, Request $request): JsonResource
    {
        // Only admins can view audit logs
        $user = $request->user();
        $accountUser = $account->users()->where('user_id', $user->id)->first();
        if (! $accountUser || $accountUser->pivot->role < 2) {
            abort(403, 'Only admins can view audit logs');
        }

        $logs = DB::table('audit_logs')
            ->where('account_id', $account->id)
            ->where('auditable_type', $type)
            ->where('auditable_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate();

        return JsonResource::collection($logs);
    }
}
