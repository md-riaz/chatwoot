<?php

namespace App\Http\Controllers\Api\V1\Channels;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Services\Channels\Facebook\FacebookService;
use App\Jobs\Channels\ProcessFacebookWebhookJob;

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

        // Create channel record for Facebook page
        $fb = \App\Models\Channels\FacebookPage::create([
            'account_id' => $account->id,
            'page_id' => $validated['page_id'],
            'page_access_token' => $validated['page_access_token'],
            'user_access_token' => $request->input('user_access_token') ?? null,
        ]);

        // Create the inbox and associate the channel
        $inbox = Inbox::create([
            'name' => $validated['name'] ?? 'Facebook Page',
            'account_id' => $account->id,
            'channel_type' => 'Channel::FacebookPage',
            'channel_id' => $fb->id,
        ]);

        // Dispatch subscription job so it can retry without blocking the request
        try {
            \App\Jobs\Channels\SubscribeFacebookPageJob::dispatch($inbox->id);
        } catch (\Throwable $e) {
            Log::warning('Failed to dispatch SubscribeFacebookPageJob', ['error' => $e->getMessage()]);
        }

        return response()->json(['data' => $inbox->load('channel')], 201);
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
        // Verify X-Hub-Signature (sha256) if present
        $appSecret = config('services.facebook.app_secret');
        $signature = $request->header('X-Hub-Signature-256') ?? $request->header('X-Hub-Signature');
        $raw = $request->getContent();

        if ($signature && $appSecret) {
            // signature format: sha256=hex
            if (str_starts_with($signature, 'sha256=')) {
                $hash = substr($signature, 7);
                $expected = hash_hmac('sha256', $raw, $appSecret);
                if (! hash_equals($expected, $hash)) {
                    Log::warning('Facebook webhook signature mismatch');
                    return response()->json(['error' => 'invalid signature'], 401);
                }
            }
        }

        $payload = json_decode($raw, true);
        if (! is_array($payload)) {
            return response()->json(['error' => 'invalid payload'], 400);
        }

        $service = new FacebookService();

        // Dispatch processing job with raw payload; the job will delegate to the service.
        try {
            ProcessFacebookWebhookJob::dispatch($payload);
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch ProcessFacebookWebhookJob', ['error' => $e->getMessage()]);
        }

        return response()->json(['status' => 'queued']);
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
    public function initiateAuthorization(Request $request, Account $account): JsonResponse
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

        // Create channel record and associate with inbox
        $fb = \App\Models\Channels\FacebookPage::create([
            'account_id' => $account->id,
            'page_id' => $validated['page_id'],
            'page_access_token' => $validated['page_access_token'],
        ]);

        $inbox = Inbox::create([
            'name' => $validated['name'],
            'account_id' => $account->id,
            'channel_type' => 'Channel::FacebookPage',
            'channel_id' => $fb->id,
        ]);

        try {
            \App\Jobs\Channels\SubscribeFacebookPageJob::dispatch($inbox->id);
        } catch (\Throwable $e) {
            Log::warning('Failed to dispatch SubscribeFacebookPageJob (callback)', ['error' => $e->getMessage()]);
        }

        return response()->json(['data' => $inbox->load('channel')], 201);
    }
}
