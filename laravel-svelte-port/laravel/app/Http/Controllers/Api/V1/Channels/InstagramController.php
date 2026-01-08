<?php

namespace App\Http\Controllers\Api\V1\Channels;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Channels\Instagram;
use App\Models\Inbox;
use App\Jobs\Channels\ProcessInstagramWebhookJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstagramController extends Controller
{
    /**
     * Create a new Instagram channel inbox.
     */
    public function create(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'instagram_id' => 'required|string|unique:channel_instagram,instagram_id',
            'access_token' => 'required|string',
            'expires_at' => 'required|date',
            'name' => 'required|string|max:255',
        ]);

        $channel = Instagram::create([
            'account_id' => $account->id,
            'instagram_id' => $validated['instagram_id'],
            'access_token' => $validated['access_token'],
            'expires_at' => $validated['expires_at'],
        ]);

        $inbox = Inbox::create([
            'account_id' => $account->id,
            'name' => $validated['name'],
            'channel_type' => Instagram::class,
            'channel_id' => $channel->id,
        ]);

        return response()->json(['data' => $inbox->load('channel')], 201);
    }

    /**
     * Update an Instagram channel inbox.
     */
    public function update(Request $request, Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);
        abort_unless($inbox->channel_type === Instagram::class, 404);

        $validated = $request->validate([
            'access_token' => 'nullable|string',
            'expires_at' => 'nullable|date',
            'name' => 'string|max:255',
        ]);

        if (isset($validated['name'])) {
            $inbox->update(['name' => $validated['name']]);
        }

        $channelData = collect($validated)->only(['access_token', 'expires_at'])->filter()->toArray();
        if (! empty($channelData)) {
            $inbox->channel->update($channelData);
        }

        return response()->json(['data' => $inbox->fresh()->load('channel')]);
    }

    /**
     * Verify Instagram webhook.
     */
    public function verifyWebhook(Request $request): JsonResponse
    {
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        $verifyToken = config('services.instagram.verify_token');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            return response()->json((int) $challenge);
        }

        return response()->json(['error' => 'Invalid verification token'], 403);
    }

    /**
     * Handle Instagram webhook events.
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();

        ProcessInstagramWebhookJob::dispatch($payload);

        return response()->json(['success' => true]);
    }

    /**
     * Authorization callback for Instagram OAuth.
     */
    public function authorize(Request $request, Account $account): JsonResponse
    {
        // TODO: Implement OAuth flow
        return response()->json([
            'redirect_url' => 'https://api.instagram.com/oauth/authorize',
        ]);
    }

    /**
     * Callback from Instagram OAuth.
     */
    public function callback(Request $request, Account $account): JsonResponse
    {
        $code = $request->get('code');

        // TODO: Exchange code for access token

        return response()->json(['success' => true]);
    }
}
