<?php

namespace App\Http\Controllers\Api\V1\Channels;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FacebookController extends Controller
{
    /**
     * Create a Facebook channel.
     */
    public function create(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'page_access_token' => 'required|string',
            'page_id' => 'required|string',
            'name' => 'string|max:255',
        ]);

        // Create the inbox with Facebook channel
        $inbox = Inbox::create([
            'name' => $validated['name'] ?? 'Facebook Page',
            'account_id' => $account->id,
            'channel_type' => 'Channel::FacebookPage',
        ]);

        return response()->json(['data' => $inbox], 201);
    }

    /**
     * Update Facebook channel settings.
     */
    public function update(Request $request, Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);
        abort_unless($inbox->channel_type === 'Channel::FacebookPage', 400);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'page_access_token' => 'string',
        ]);

        $inbox->update(['name' => $validated['name'] ?? $inbox->name]);

        return response()->json(['data' => $inbox]);
    }

    /**
     * Receive webhook from Facebook.
     */
    public function webhook(Request $request): JsonResponse
    {
        // Verify signature
        // Process messages/events
        // Dispatch jobs for processing

        return response()->json(['status' => 'received']);
    }

    /**
     * Verify webhook (for Facebook verification).
     */
    public function verifyWebhook(Request $request): mixed
    {
        $mode = $request->get('hub.mode');
        $token = $request->get('hub.verify_token');
        $challenge = $request->get('hub.challenge');

        if ($mode === 'subscribe' && $token === config('services.facebook.verify_token')) {
            return response($challenge, 200);
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }

    /**
     * Initiate Facebook OAuth flow.
     */
    public function authorize(Request $request, Account $account): JsonResponse
    {
        $redirectUri = config('services.facebook.redirect_uri', url('/callback'));
        $appId = config('services.facebook.app_id', '');

        $oauthUrl = "https://www.facebook.com/v18.0/dialog/oauth?" . http_build_query([
            'client_id' => $appId,
            'redirect_uri' => $redirectUri,
            'scope' => 'pages_show_list,pages_messaging,pages_manage_metadata',
            'response_type' => 'code',
        ]);

        return response()->json([
            'authorization_url' => $oauthUrl,
        ]);
    }

    /**
     * Get Facebook pages for authorization.
     */
    public function pages(Request $request, Account $account): JsonResponse
    {
        // Access token can come from session or request
        // In production, fetch pages from Facebook Graph API
        $pages = [];

        return response()->json(['data' => $pages]);
    }

    /**
     * Create Facebook inbox from OAuth callback.
     */
    public function createFromCallback(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'page_id' => 'required|string',
            'page_access_token' => 'required|string',
            'name' => 'required|string|max:255',
        ]);

        $inbox = Inbox::create([
            'name' => $validated['name'],
            'account_id' => $account->id,
            'channel_type' => 'Channel::FacebookPage',
        ]);

        return response()->json(['data' => $inbox], 201);
    }
}
