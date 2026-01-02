<?php

namespace App\Http\Controllers\Api\V1\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Integration;
use App\Models\Conversation;
use App\Services\Integrations\OpenAIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OpenAIController extends Controller
{
    /**
     * Get OpenAI integration settings.
     */
    public function show(Account $account): JsonResponse
    {
        $integration = Integration::ofType('openai')->where('account_id', $account->id)->first();

        return response()->json(['data' => $integration]);
    }

    /**
     * Create/Update OpenAI integration.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'api_key' => 'required|string',
            'model' => 'string|in:gpt-4,gpt-4-turbo,gpt-3.5-turbo,gpt-4o,gpt-4o-mini',
            'temperature' => 'numeric|min:0|max:2',
            'max_tokens' => 'integer|min:1|max:4000',
        ]);

        $integration = Integration::updateOrCreate(
            ['account_id' => $account->id, 'type' => 'openai'],
            [
                'settings' => [
                    'model' => $validated['model'] ?? 'gpt-4o-mini',
                    'temperature' => $validated['temperature'] ?? 0.7,
                    'max_tokens' => $validated['max_tokens'] ?? 1000,
                ],
                'credentials' => [
                    'api_key' => $validated['api_key'],
                ],
                'active' => true,
            ]
        );

        return response()->json(['data' => $integration], 201);
    }

    /**
     * Update OpenAI integration settings.
     */
    public function update(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'enabled' => 'boolean',
            'model' => 'string|in:gpt-4,gpt-4-turbo,gpt-3.5-turbo,gpt-4o,gpt-4o-mini',
            'temperature' => 'numeric|min:0|max:2',
            'max_tokens' => 'integer|min:1|max:4000',
        ]);

        $integration = Integration::ofType('openai')->where('account_id', $account->id)->firstOrFail();

        if (array_key_exists('enabled', $validated)) {
            $integration->active = (bool) $validated['enabled'];
        }

        $settings = $integration->settings ?? [];
        if (isset($validated['model'])) {
            $settings['model'] = $validated['model'];
        }
        if (isset($validated['temperature'])) {
            $settings['temperature'] = $validated['temperature'];
        }
        if (isset($validated['max_tokens'])) {
            $settings['max_tokens'] = $validated['max_tokens'];
        }

        $integration->settings = $settings;
        $integration->save();

        return response()->json(['data' => $integration]);
    }

    /**
     * Delete OpenAI integration.
     */
    public function destroy(Account $account): JsonResponse
    {
        $integration = Integration::ofType('openai')->where('account_id', $account->id)->first();
        if ($integration) {
            $integration->delete();
        }

        return response()->json(null, 204);
    }

    /**
     * Generate reply suggestion for a conversation.
     */
    public function suggestReply(Request $request, Account $account, Conversation $conversation): JsonResponse
    {
        $integration = Integration::ofType('openai')->where('account_id', $account->id)->firstOrFail();
        $service = new OpenAIService($integration);

        $additionalContext = $request->input('context');
        $result = $service->suggestReply($conversation, $additionalContext);

        return response()->json(['data' => $result]);
    }

    /**
     * Summarize a conversation.
     */
    public function summarize(Request $request, Account $account, Conversation $conversation): JsonResponse
    {
        $integration = Integration::ofType('openai')->where('account_id', $account->id)->firstOrFail();
        $service = new OpenAIService($integration);

        $result = $service->summarize($conversation);

        return response()->json(['data' => $result]);
    }

    /**
     * Improve message tone.
     */
    public function improveTone(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'tone' => 'string|in:professional,friendly,formal,empathetic,concise',
        ]);

        $integration = Integration::ofType('openai')->where('account_id', $account->id)->firstOrFail();
        $service = new OpenAIService($integration);

        $result = $service->improveTone(
            $validated['message'],
            $validated['tone'] ?? 'professional'
        );

        return response()->json(['data' => $result]);
    }

    /**
     * Expand a brief message.
     */
    public function expand(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $integration = Integration::ofType('openai')->where('account_id', $account->id)->firstOrFail();
        $service = new OpenAIService($integration);

        $result = $service->expand($validated['message']);

        return response()->json(['data' => $result]);
    }

    /**
     * Fix grammar and spelling.
     */
    public function fixGrammar(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $integration = Integration::ofType('openai')->where('account_id', $account->id)->firstOrFail();
        $service = new OpenAIService($integration);

        $result = $service->fixGrammar($validated['message']);

        return response()->json(['data' => $result]);
    }

    /**
     * Translate a message.
     */
    public function translate(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'target_language' => 'required|string',
        ]);

        $integration = Integration::ofType('openai')->where('account_id', $account->id)->firstOrFail();
        $service = new OpenAIService($integration);

        $result = $service->translate(
            $validated['message'],
            $validated['target_language']
        );

        return response()->json(['data' => $result]);
    }

    /**
     * Detect sentiment of a message.
     */
    public function detectSentiment(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $integration = Integration::ofType('openai')->where('account_id', $account->id)->firstOrFail();
        $service = new OpenAIService($integration);

        $result = $service->detectSentiment($validated['message']);

        return response()->json(['data' => $result]);
    }

    /**
     * Transcribe audio file.
     */
    public function transcribeAudio(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'audio_file' => 'required|file|mimes:mp3,wav,m4a,ogg|max:25600', // 25MB max
        ]);

        $integration = Integration::ofType('openai')->where('account_id', $account->id)->firstOrFail();
        $service = new OpenAIService($integration);

        $audioFile = $validated['audio_file'];
        $tempPath = $audioFile->store('temp/audio');
        $fullPath = storage_path("app/{$tempPath}");

        try {
            $result = $service->transcribeAudio($fullPath);
            return response()->json(['data' => $result]);
        } finally {
            // Clean up temp file
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    /**
     * Moderate content using OpenAI.
     */
    public function moderateContent(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $integration = Integration::ofType('openai')->where('account_id', $account->id)->firstOrFail();
        $service = new OpenAIService($integration);

        $result = $service->moderateContent($validated['content']);

        return response()->json(['data' => $result]);
    }

    /**
     * Get usage statistics.
     */
    public function usageStats(Request $request, Account $account): JsonResponse
    {
        $period = $request->input('period', 'month');

        $integration = Integration::ofType('openai')->where('account_id', $account->id)->firstOrFail();
        $service = new OpenAIService($integration);

        $stats = $service->getUsageStats($period);

        return response()->json(['data' => $stats]);
    }

    /**
     * List available models.
     */
    public function models(Account $account): JsonResponse
    {
        $integration = Integration::ofType('openai')->where('account_id', $account->id)->firstOrFail();
        $service = new OpenAIService($integration);

        $models = $service->listModels();

        return response()->json(['data' => $models]);
    }

    /**
     * Test OpenAI connection.
     */
    public function testConnection(Account $account): JsonResponse
    {
        try {
            $integration = Integration::ofType('openai')->where('account_id', $account->id)->firstOrFail();
            $service = new OpenAIService($integration);

            // Test with a simple completion
            $result = $service->chat([
                ['role' => 'user', 'content' => 'Hello, this is a connection test.'],
            ], ['max_tokens' => 10]);

            return response()->json([
                'data' => [
                    'connected' => $result['success'],
                    'model' => $service->model ?? 'unknown',
                    'test_response' => $result['success'] ? $result['content'] : null,
                    'error' => $result['success'] ? null : $result['error'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => [
                    'connected' => false,
                    'error' => $e->getMessage(),
                ],
            ]);
        }
    }

    /**
     * Batch process multiple requests.
     */
    public function batchProcess(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'requests' => 'required|array|max:10', // Limit to 10 requests per batch
            'requests.*.messages' => 'required|array',
            'requests.*.feature' => 'string',
            'requests.*.options' => 'array',
        ]);

        $integration = Integration::ofType('openai')->where('account_id', $account->id)->firstOrFail();
        $service = new OpenAIService($integration);

        $results = $service->batchProcess($validated['requests']);

        return response()->json(['data' => $results]);
    }
}