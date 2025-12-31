<?php

namespace App\Http\Controllers\Api\V1\Channels;

use App\Http\Controllers\Controller;
use App\Jobs\Channels\ProcessTwitterWebhookJob;
use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TwitterController extends Controller
{
    /**
     * Create a Twitter channel.
     */
    public function create(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'oauth_token' => 'required|string',
            'oauth_token_secret' => 'required|string',
            'name' => 'string|max:255',
        ]);

        // Create the inbox with Twitter channel
        $inbox = Inbox::create([
            'name' => $validated['name'] ?? 'Twitter',
            'account_id' => $account->id,
            'channel_type' => 'Channel::Twitter',
        ]);

        return response()->json(['data' => $inbox], 201);
    }

    /**
     * Update Twitter channel settings.
     */
    public function update(Request $request, Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);
        abort_unless($inbox->channel_type === 'Channel::Twitter', 400);

        $validated = $request->validate([
            'name' => 'string|max:255',
        ]);

        $inbox->update(['name' => $validated['name'] ?? $inbox->name]);

        return response()->json(['data' => $inbox]);
    }

    /**
     * Get OAuth URL for Twitter authorization.
     */
    public function authorize(Account $account): JsonResponse
    {
        // Generate OAuth URL for Twitter
        $oauthUrl = 'https://api.twitter.com/oauth/authorize?oauth_token=...';

        return response()->json(['data' => ['oauth_url' => $oauthUrl]]);
    }

    /**
     * Handle OAuth callback from Twitter.
     */
    public function callback(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'oauth_token' => 'required|string',
            'oauth_verifier' => 'required|string',
        ]);

        // Exchange tokens and create channel
        
        return response()->json(['message' => 'Twitter channel connected']);
    }

    /**
     * Receive webhook from Twitter (Account Activity API).
     */
    public function webhook(Request $request): JsonResponse
    {
        ProcessTwitterWebhookJob::dispatch($request->all());

        return response()->json(['status' => 'ok']);
    }

    /**
     * Handle CRC check from Twitter.
     */
    public function crcCheck(Request $request): JsonResponse
    {
        $crcToken = $request->get('crc_token');
        
        // Generate response token
        $responseToken = hash_hmac('sha256', $crcToken, config('services.twitter.consumer_secret'), true);
        
        return response()->json([
            'response_token' => 'sha256=' . base64_encode($responseToken)
        ]);
    }
}
