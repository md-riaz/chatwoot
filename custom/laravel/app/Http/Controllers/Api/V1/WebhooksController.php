<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Webhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebhooksController extends Controller
{
    /**
     * Display a listing of webhooks for an account.
     * Requires admin role.
     */
    public function index(Request $request, Account $account): JsonResource
    {
        $this->ensureAdmin($request, $account);
        
        $webhooks = Webhook::where('account_id', $account->id)->paginate();

        return JsonResource::collection($webhooks);
    }

    /**
     * Store a newly created webhook.
     * Requires admin role.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $validated = $request->validate([
            'url' => 'required|url',
            'subscriptions' => 'required|array',
            'subscriptions.*' => 'string',
        ]);

        $webhook = Webhook::create([
            ...$validated,
            'account_id' => $account->id,
        ]);

        return response()->json(['data' => $webhook], 201);
    }

    /**
     * Display the specified webhook.
     * Requires admin role.
     */
    public function show(Request $request, Account $account, Webhook $webhook): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        abort_unless($webhook->account_id === $account->id, 404);

        return response()->json(['data' => $webhook]);
    }

    /**
     * Update the specified webhook.
     * Requires admin role.
     */
    public function update(Request $request, Account $account, Webhook $webhook): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        abort_unless($webhook->account_id === $account->id, 404);

        $validated = $request->validate([
            'url' => 'url',
            'subscriptions' => 'array',
            'subscriptions.*' => 'string',
        ]);

        $webhook->update($validated);

        return response()->json(['data' => $webhook]);
    }

    /**
     * Remove the specified webhook.
     * Requires admin role.
     */
    public function destroy(Request $request, Account $account, Webhook $webhook): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        abort_unless($webhook->account_id === $account->id, 404);

        $webhook->delete();

        return response()->json(null, 204);
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
