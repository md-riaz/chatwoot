<?php

namespace App\Http\Controllers\Api\V1\Widget;

use App\Models\Campaign;
use App\Models\Inbox;
use App\Models\ContactInbox;
use App\Models\Conversation;
use App\Models\Message;
use App\Actions\Message\CreateMessageAction;
use App\Data\Message\MessageData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $inbox = Inbox::where('channel_type', 'Channel::WebWidget')
            ->whereHas('channel', function ($query) use ($validated) {
                $query->where('website_token', $validated['website_token']);
            })
            ->first();

        if (!$inbox) {
            return response()->json(['error' => 'Invalid website token'], 404);
        }

        // Check if campaigns feature is enabled for the account
        if (!$inbox->account->isFeatureEnabled('campaigns')) {
            return response()->json(['data' => []]);
        }

        // Get active ongoing campaigns for this inbox
        $campaigns = Campaign::where('inbox_id', $inbox->id)
            ->where('campaign_type', Campaign::TYPE_ONGOING)
            ->where('enabled', true)
            ->with(['sender'])
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
                        'available_name' => $campaign->sender->name,
                        'avatar_url' => $campaign->sender->getAvatarUrl(),
                        'availability_status' => 'online', // Default for campaigns
                    ] : null,
                    'trigger_rules' => $campaign->trigger_rules ?? [],
                    'trigger_only_during_business_hours' => $campaign->trigger_only_during_business_hours ?? false,
                ];
            }),
        ]);
    }

    /**
     * Trigger a campaign execution.
     * POST /api/v1/widget/campaigns/trigger
     */
    public function trigger(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'website_token' => 'required|string',
            'campaign_id' => 'required|integer',
            'custom_attributes' => 'nullable|array',
            'contact_identifier' => 'nullable|string',
            'contact_identifier_hash' => 'nullable|string',
        ]);

        try {
            // Find the inbox by website token
            $inbox = Inbox::where('channel_type', 'Channel::WebWidget')
                ->whereHas('channel', function ($query) use ($validated) {
                    $query->where('website_token', $validated['website_token']);
                })
                ->first();

            if (!$inbox) {
                return response()->json(['error' => 'Invalid website token'], 404);
            }

            // Find the campaign
            $campaign = Campaign::where('id', $validated['campaign_id'])
                ->where('inbox_id', $inbox->id)
                ->where('enabled', true)
                ->where('campaign_type', Campaign::TYPE_ONGOING)
                ->first();

            if (!$campaign) {
                return response()->json(['error' => 'Campaign not found or not active'], 404);
            }

            // Get or create contact inbox from the current session
            $contactInbox = $this->getContactInbox();
            
            if (!$contactInbox) {
                return response()->json(['error' => 'Contact session not found'], 400);
            }

            // Check if conversation already exists for this contact
            if ($contactInbox->conversations()->exists()) {
                return response()->json(['error' => 'Conversation already exists'], 400);
            }

            // Create conversation and message in transaction
            DB::transaction(function () use ($campaign, $contactInbox, $validated) {
                // Create conversation
                $conversation = Conversation::create([
                    'account_id' => $campaign->account_id,
                    'inbox_id' => $campaign->inbox_id,
                    'contact_id' => $contactInbox->contact_id,
                    'contact_inbox_id' => $contactInbox->id,
                    'campaign_id' => $campaign->id,
                    'status' => 'open',
                    'custom_attributes' => $validated['custom_attributes'] ?? [],
                ]);

                // Create campaign message
                $messageData = MessageData::from([
                    'conversation_id' => $conversation->id,
                    'inbox_id' => $campaign->inbox_id,
                    'sender_id' => $campaign->sender_id,
                    'sender_type' => \App\Models\User::class,
                    'message_type' => Message::TYPE_OUTGOING,
                    'content' => $campaign->message,
                    'content_type' => Message::CONTENT_TEXT,
                    'content_attributes' => [
                        'campaign_id' => $campaign->id,
                    ],
                    'private' => false,
                ]);

                CreateMessageAction::run($messageData);
            });

            return response()->json(['message' => 'Campaign triggered successfully']);

        } catch (\Exception $e) {
            Log::error('Campaign trigger failed', [
                'campaign_id' => $validated['campaign_id'],
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to trigger campaign'], 500);
        }
    }
}
