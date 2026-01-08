<?php

namespace App\Http\Controllers\Api\V1\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DialogflowController extends Controller
{
    /**
     * Get Dialogflow integration settings.
     */
    public function show(Account $account): JsonResponse
    {
        $integration = null; // Would fetch from integrations table

        return response()->json(['data' => $integration]);
    }

    /**
     * Create Dialogflow integration.
     */
    public function create(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|string',
            'credentials' => 'required|array',
            'inbox_ids' => 'array',
            'inbox_ids.*' => 'exists:inboxes,id',
        ]);

        // Create Dialogflow integration
        // Store credentials securely

        return response()->json(['message' => 'Dialogflow connected successfully'], 201);
    }

    /**
     * Update Dialogflow integration.
     */
    public function update(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'string',
            'credentials' => 'array',
            'inbox_ids' => 'array',
            'inbox_ids.*' => 'exists:inboxes,id',
            'enabled' => 'boolean',
        ]);

        // Update Dialogflow integration

        return response()->json(['message' => 'Dialogflow settings updated']);
    }

    /**
     * Disconnect Dialogflow integration.
     */
    public function destroy(Account $account): JsonResponse
    {
        // Remove Dialogflow integration

        return response()->json(null, 204);
    }

    /**
     * Test Dialogflow connection.
     */
    public function test(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        // Send test message to Dialogflow
        $response = [
            'intent' => 'test_intent',
            'confidence' => 0.95,
            'fulfillment_text' => 'Test response from Dialogflow',
        ];

        return response()->json(['data' => $response]);
    }
}
