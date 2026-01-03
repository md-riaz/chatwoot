<?php

namespace App\Services\Integrations;

use App\Models\Integration;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected string $apiUrl = 'https://api.openai.com/v1';
    protected ?string $apiKey;
    protected string $model = 'gpt-4';

    public function __construct(?Integration $integration = null)
    {
        if ($integration && $integration->type === 'openai') {
            $this->apiKey = $integration->credentials['api_key'] ?? null;
            $this->model = $integration->settings['model'] ?? 'gpt-4';
        } else {
            $this->apiKey = config('services.openai.api_key');
        }
    }

    /**
     * Generate a reply suggestion based on conversation context
     */
    public function suggestReply(Conversation $conversation): array
    {
        $messages = $this->buildConversationMessages($conversation);

        return $this->chat([
            ['role' => 'system', 'content' => 'You are a helpful support agent. Based on the conversation history, suggest a professional and helpful reply to the customer.'],
            ...$messages,
            ['role' => 'user', 'content' => 'Please suggest a reply to help this customer.'],
        ]);
    }

    /**
     * Summarize a conversation
     */
    public function summarize(Conversation $conversation): array
    {
        $messages = $this->buildConversationMessages($conversation);

        $conversationText = collect($messages)
            ->map(fn($m) => "{$m['role']}: {$m['content']}")
            ->join("\n");

        return $this->chat([
            [
                'role' => 'system',
                'content' => 'You are a helpful assistant that summarizes customer support conversations. Provide a concise summary including: the main issue, key points discussed, current status, and any pending actions.',
            ],
            [
                'role' => 'user',
                'content' => "Please summarize this conversation:\n\n{$conversationText}",
            ],
        ]);
    }

    /**
     * Improve the tone of a message
     */
    public function improveTone(string $message, string $targetTone = 'professional'): array
    {
        $toneDescriptions = [
            'professional' => 'professional and courteous',
            'friendly' => 'warm, friendly, and approachable',
            'formal' => 'formal and businesslike',
        ];

        $toneDescription = $toneDescriptions[$targetTone] ?? $toneDescriptions['professional'];

        return $this->chat([
            [
                'role' => 'system',
                'content' => "You are a writing assistant that improves the tone of customer support messages. Rewrite the given message to be {$toneDescription}. Maintain the original meaning and information.",
            ],
            [
                'role' => 'user',
                'content' => "Please improve the tone of this message:\n\n{$message}",
            ],
        ]);
    }

    /**
     * Fix grammar and spelling
     */
    public function fixGrammar(string $message): array
    {
        return $this->chat([
            [
                'role' => 'system',
                'content' => 'You are a writing assistant. Fix any grammar, spelling, or punctuation errors in the given text. Return only the corrected text without explanations.',
            ],
            [
                'role' => 'user',
                'content' => $message,
            ],
        ]);
    }

    /**
     * Core chat completion method
     */
    public function chat(array $messages, array $options = []): array
    {
        if (!$this->apiKey) {
            return ['success' => false, 'error' => 'OpenAI API key not configured'];
        }

        try {
            $payload = array_merge([
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 1000,
            ], $options);

            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post("{$this->apiUrl}/chat/completions", $payload);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');

                return [
                    'success' => true,
                    'content' => trim($content),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('error.message', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI API request failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Build messages array from conversation
     */
    protected function buildConversationMessages(Conversation $conversation): array
    {
        return $conversation->messages()
            ->orderBy('created_at')
            ->limit(20)
            ->get()
            ->map(function (Message $message) {
                $role = $message->message_type === 0 ? 'user' : 'assistant';
                return [
                    'role' => $role,
                    'content' => $message->content ?? '',
                ];
            })
            ->filter(fn($m) => !empty($m['content']))
            ->values()
            ->toArray();
    }
}
