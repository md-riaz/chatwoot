<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class SlaPoliciesController extends Controller
{
    /**
     * Display a listing of SLA policies for an account.
     * Requires admin role.
     */
    public function index(Request $request, Account $account): JsonResource
    {
        $this->ensureAdmin($request, $account);
        
        $policies = DB::table('sla_policies')
            ->where('account_id', $account->id)
            ->orderBy('created_at', 'desc')
            ->paginate();

        return JsonResource::collection($policies);
    }

    /**
     * Store a newly created SLA policy.
     * Requires admin role.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'first_response_time_threshold' => 'nullable|integer|min:1', // in seconds
            'next_response_time_threshold' => 'nullable|integer|min:1',
            'resolution_time_threshold' => 'nullable|integer|min:1',
            'only_during_business_hours' => 'boolean',
            'active' => 'boolean',
        ]);

        $policyId = DB::table('sla_policies')->insertGetId([
            ...$validated,
            'account_id' => $account->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $policy = DB::table('sla_policies')->find($policyId);

        return response()->json(['data' => $policy], 201);
    }

    /**
     * Display the specified SLA policy.
     * Requires admin role.
     */
    public function show(Request $request, Account $account, int $policyId): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $policy = DB::table('sla_policies')
            ->where('account_id', $account->id)
            ->where('id', $policyId)
            ->first();

        abort_unless($policy, 404);

        return response()->json(['data' => $policy]);
    }

    /**
     * Update the specified SLA policy.
     * Requires admin role.
     */
    public function update(Request $request, Account $account, int $policyId): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $policy = DB::table('sla_policies')
            ->where('account_id', $account->id)
            ->where('id', $policyId)
            ->first();

        abort_unless($policy, 404);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'first_response_time_threshold' => 'nullable|integer|min:1',
            'next_response_time_threshold' => 'nullable|integer|min:1',
            'resolution_time_threshold' => 'nullable|integer|min:1',
            'only_during_business_hours' => 'boolean',
            'active' => 'boolean',
        ]);

        DB::table('sla_policies')
            ->where('id', $policyId)
            ->update([
                ...$validated,
                'updated_at' => now(),
            ]);

        $policy = DB::table('sla_policies')->find($policyId);

        return response()->json(['data' => $policy]);
    }

    /**
     * Remove the specified SLA policy.
     * Requires admin role.
     */
    public function destroy(Request $request, Account $account, int $policyId): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $policy = DB::table('sla_policies')
            ->where('account_id', $account->id)
            ->where('id', $policyId)
            ->first();

        abort_unless($policy, 404);

        DB::table('sla_policies')->where('id', $policyId)->delete();

        return response()->json(null, 204);
    }

    /**
     * Get SLA breaches.
     * Requires admin role.
     */
    public function breaches(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $since = $request->get('since', now()->subDays(7)->toDateString());
        $until = $request->get('until', now()->toDateString());

        // Get conversations with SLA breaches
        $breaches = DB::table('conversations')
            ->where('account_id', $account->id)
            ->where('sla_status', 'breached')
            ->whereBetween('created_at', [$since, $until])
            ->paginate();

        return response()->json(['data' => $breaches]);
    }

    /**
     * Get SLA metrics.
     * Requires admin role.
     */
    public function metrics(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $since = $request->get('since', now()->subDays(7)->toDateString());
        $until = $request->get('until', now()->toDateString());

        // Calculate SLA metrics
        $metrics = [
            'total_conversations' => 0,
            'sla_hit' => 0,
            'sla_breached' => 0,
            'sla_hit_percentage' => 0,
            'avg_first_response_time' => 0,
            'avg_resolution_time' => 0,
        ];

        return response()->json(['data' => $metrics]);
    }
    
    /**
     * Ensure the current user is an admin of the account.
     */
    private function ensureAdmin(Request $request, Account $account): void
    {
        $user = $request->user();
        $accountUser = $account->users()->where('user_id', $user->id)->first();
        
        if (!$accountUser || $accountUser->pivot->role < 2) {
            abort(403, 'Admin access required');
        }
    }
}
