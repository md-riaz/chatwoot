<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Campaign\ScheduleCampaignSendAction;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignsController extends Controller
{
    /**
     * Display a listing of campaigns for an account.
     */
    public function index(Account $account): JsonResource
    {
        $campaigns = Campaign::where('account_id', $account->id)->paginate();

        return JsonResource::collection($campaigns);
    }

    /**
     * Store a newly created campaign.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'message' => 'required|string',
            'enabled' => 'boolean',
            'campaign_type' => 'nullable|integer',
            'trigger_only_during_business_hours' => 'boolean',
            'inbox_id' => 'required|exists:inboxes,id',
            'sender_id' => 'nullable|exists:users,id',
            'scheduled_at' => 'nullable|date',
            'audience' => 'nullable|array',
            'trigger_rules' => 'nullable|array',
        ]);

        $campaign = Campaign::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'message' => $validated['message'],
            'enabled' => $validated['enabled'] ?? true,
            'campaign_type' => $validated['campaign_type'] ?? 0,
            'campaign_status' => Campaign::STATUS_ACTIVE,
            'trigger_only_during_business_hours' => $validated['trigger_only_during_business_hours'] ?? false,
            'inbox_id' => $validated['inbox_id'],
            'sender_id' => $validated['sender_id'] ?? null,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'audience' => $validated['audience'] ?? [],
            'trigger_rules' => $validated['trigger_rules'] ?? [],
            'account_id' => $account->id,
        ]);

        ScheduleCampaignSendAction::run($campaign);

        return response()->json(['data' => $campaign], 201);
    }

    /**
     * Display the specified campaign.
     */
    public function show(Account $account, Campaign $campaign): JsonResponse
    {
        abort_unless($campaign->account_id === $account->id, 404);

        return response()->json(['data' => $campaign]);
    }

    /**
     * Update the specified campaign.
     */
    public function update(Request $request, Account $account, Campaign $campaign): JsonResponse
    {
        abort_unless($campaign->account_id === $account->id, 404);

        $validated = $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'message' => 'string',
            'enabled' => 'boolean',
            'trigger_only_during_business_hours' => 'boolean',
            'inbox_id' => 'exists:inboxes,id',
            'sender_id' => 'nullable|exists:users,id',
            'scheduled_at' => 'nullable|date',
            'audience' => 'nullable|array',
            'trigger_rules' => 'nullable|array',
        ]);

        $campaign->update($validated);

        $schedulingFieldsChanged = $campaign->wasChanged(['scheduled_at', 'message', 'enabled', 'trigger_rules', 'audience']);
        $wasReEnabled = $campaign->wasChanged('enabled') && $campaign->enabled;

        if ($campaign->enabled && ($schedulingFieldsChanged || $wasReEnabled)) {
            ScheduleCampaignSendAction::run($campaign);
        }

        return response()->json(['data' => $campaign]);
    }

    /**
     * Remove the specified campaign.
     */
    public function destroy(Account $account, Campaign $campaign): JsonResponse
    {
        abort_unless($campaign->account_id === $account->id, 404);

        $campaign->delete();

        return response()->json(null, 204);
    }
}
