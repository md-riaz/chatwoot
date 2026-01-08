<?php

namespace App\Http\Controllers\Api\V1\Widget;

use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignsController extends BaseController
{
    /**
     * Get active campaigns for the inbox.
     * GET /api/v1/widget/campaigns
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'website_token' => 'required|string',
        ]);

        // Find the inbox by website token
        $inbox = \App\Models\Inbox::where('channel_type', 'Channel::WebWidget')
            ->whereHas('channel', function ($query) use ($validated) {
                $query->where('website_token', $validated['website_token']);
            })
            ->first();

        if (!$inbox) {
            return response()->json(['error' => 'Invalid website token'], 404);
        }

        // Get active campaigns for this inbox
        $campaigns = Campaign::where('inbox_id', $inbox->id)
            ->where('campaign_type', 'ongoing')
            ->where('enabled', true)
            ->get();

        return response()->json([
            'data' => $campaigns->map(function ($campaign) {
                return [
                    'id' => $campaign->id,
                    'title' => $campaign->title,
                    'message' => $campaign->message,
                    'sender' => $campaign->sender ? [
                        'id' => $campaign->sender->id,
                        'name' => $campaign->sender->name,
                        'avatar_url' => $campaign->sender->getAvatarUrl(),
                    ] : null,
                    'trigger_rules' => $campaign->trigger_rules ?? [],
                    'trigger_only_during_business_hours' => $campaign->trigger_only_during_business_hours ?? false,
                ];
            }),
        ]);
    }
}
