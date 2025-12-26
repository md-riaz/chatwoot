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
     */
    public function index(Account $account): JsonResource
    {
        $webhooks = Webhook::where('account_id', $account->id)->paginate();

        return JsonResource::collection($webhooks);
    }

    /**
     * Store a newly created webhook.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
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
     */
    public function show(Account $account, Webhook $webhook): JsonResponse
    {
        abort_unless($webhook->account_id === $account->id, 404);

        return response()->json(['data' => $webhook]);
    }

    /**
     * Update the specified webhook.
     */
    public function update(Request $request, Account $account, Webhook $webhook): JsonResponse
    {
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
     */
    public function destroy(Account $account, Webhook $webhook): JsonResponse
    {
        abort_unless($webhook->account_id === $account->id, 404);

        $webhook->delete();

        return response()->json(null, 204);
    }
}
