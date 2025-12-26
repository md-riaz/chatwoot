<?php

namespace App\Http\Controllers\Api\V1\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IntegrationsController extends Controller
{
    /**
     * List all available integrations.
     */
    public function index(Account $account): JsonResponse
    {
        $integrations = [
            [
                'id' => 'slack',
                'name' => 'Slack',
                'description' => 'Collaborate with your team on customer conversations',
                'enabled' => false,
                'logo' => '/integrations/slack.png',
            ],
            [
                'id' => 'dialogflow',
                'name' => 'Dialogflow',
                'description' => 'Integrate AI-powered chatbots',
                'enabled' => false,
                'logo' => '/integrations/dialogflow.png',
            ],
            [
                'id' => 'linear',
                'name' => 'Linear',
                'description' => 'Create and manage issues from conversations',
                'enabled' => false,
                'logo' => '/integrations/linear.png',
            ],
            [
                'id' => 'shopify',
                'name' => 'Shopify',
                'description' => 'View customer orders and details',
                'enabled' => false,
                'logo' => '/integrations/shopify.png',
            ],
            [
                'id' => 'openai',
                'name' => 'OpenAI',
                'description' => 'AI-powered response suggestions',
                'enabled' => false,
                'logo' => '/integrations/openai.png',
            ],
            [
                'id' => 'google_translate',
                'name' => 'Google Translate',
                'description' => 'Automatic message translation',
                'enabled' => false,
                'logo' => '/integrations/google_translate.png',
            ],
            [
                'id' => 'dyte',
                'name' => 'Dyte',
                'description' => 'Video and voice calls in conversations',
                'enabled' => false,
                'logo' => '/integrations/dyte.png',
            ],
        ];

        // Check which integrations are enabled for the account
        // Would query the integrations table

        return response()->json(['data' => $integrations]);
    }

    /**
     * Get integration hooks for account.
     */
    public function hooks(Account $account): JsonResource
    {
        // Get all integration hooks for the account
        $hooks = collect();

        return JsonResource::collection($hooks);
    }

    /**
     * Create an integration hook.
     */
    public function createHook(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'app_id' => 'required|string',
            'inbox_id' => 'nullable|exists:inboxes,id',
            'settings' => 'nullable|array',
        ]);

        // Create integration hook

        return response()->json(['message' => 'Hook created'], 201);
    }

    /**
     * Update an integration hook.
     */
    public function updateHook(Request $request, Account $account, int $hookId): JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'array',
            'enabled' => 'boolean',
        ]);

        // Update integration hook

        return response()->json(['message' => 'Hook updated']);
    }

    /**
     * Delete an integration hook.
     */
    public function deleteHook(Account $account, int $hookId): JsonResponse
    {
        // Delete integration hook

        return response()->json(null, 204);
    }
}
