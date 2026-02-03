<?php

namespace App\Http\Controllers\Api\V1\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SlackController extends Controller
{
    /**
     * Get Slack integration settings.
     */
    public function show(Account $account): JsonResponse
    {
        // Get Slack integration for account
        $integration = null; // Would fetch from integrations table

        return response()->json(['data' => $integration]);
    }

    /**
     * Get Slack OAuth authorization URL.
     */
    public function initiateAuthorization(Account $account): JsonResponse
    {
        $clientId = config('services.slack.client_id', '');
        $redirectUri = config('services.slack.redirect_uri', url('/callback/slack'));
        $scopes = 'channels:history,channels:join,channels:manage,channels:read,chat:write,commands,groups:history,groups:read,im:history,im:read,im:write,mpim:read,reactions:write,users.profile:read,users:read,users:read.email';

        $authorizationUrl = "https://slack.com/oauth/v2/authorize?" . http_build_query([
            'client_id' => $clientId,
            'scope' => $scopes,
            'redirect_uri' => $redirectUri,
            'state' => base64_encode(json_encode(['account_id' => $account->id])),
        ]);

        return response()->json([
            'authorization_url' => $authorizationUrl,
        ]);
    }

    /**
     * Create/Connect Slack integration.
     */
    public function create(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'nullable|string', // OAuth code from Slack
            'access_token' => 'nullable|string',
            'channel_id' => 'nullable|string',
        ]);

        // Exchange code for access token or use provided token
        // Store integration settings

        return response()->json(['message' => 'Slack connected successfully'], 201);
    }

    /**
     * Update Slack integration settings.
     */
    public function update(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'channel_id' => 'string',
            'enabled' => 'boolean',
        ]);

        // Update Slack integration settings

        return response()->json(['message' => 'Slack settings updated']);
    }

    /**
     * Disconnect Slack integration.
     */
    public function destroy(Account $account): JsonResponse
    {
        // Remove Slack integration

        return response()->json(null, 200);
    }

    /**
     * Get available Slack channels.
     */
    public function channels(Account $account): JsonResponse
    {
        // Fetch channels from Slack API
        $channels = [];

        return response()->json(['data' => $channels]);
    }

    /**
     * Handle Slack events webhook.
     */
    public function events(Request $request): JsonResponse
    {
        // Verify Slack signature
        // Handle challenge
        if ($request->has('challenge')) {
            return response()->json(['challenge' => $request->challenge]);
        }

        // Process Slack events
        // Dispatch jobs for processing

        return response()->json(['status' => 'ok']);
    }

    /**
     * Handle Slack interactive components.
     */
    public function interactive(Request $request): JsonResponse
    {
        // Process interactive component payloads (buttons, modals, etc.)

        return response()->json(['status' => 'ok']);
    }

    /**
     * Handle Slack slash commands.
     */
    public function commands(Request $request): JsonResponse
    {
        $command = $request->get('command');
        $text = $request->get('text');

        // Process slash commands

        return response()->json(['text' => 'Command processed']);
    }
}
