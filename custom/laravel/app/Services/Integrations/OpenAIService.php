<?php

namespace App\Services\Integrations;

use App\Models\Message;
use Illuminate\Support\Facades\Log;
use OpenAI\Client as OpenAIClient;

class OpenAIService
{
    protected OpenAIClient $client;

    public function __construct()
    {
        $apiKey = config('services.openai.key') ?? env('OPENAI_API_KEY');
        $this->client = new OpenAIClient(['api_key' => $apiKey]);
    }

    /**
     * Enrich a message by id using OpenAI (completion/embedding/summary etc.).
     * This is a lightweight port of Rails backend behavior; keep processing idempotent and store results on message.
     */
    public function enrichMessage(int $messageId): void
    {
        $message = Message::find($messageId);
        if (! $message) {
            Log::warning('OpenAIService::enrichMessage message not found', ['message_id' => $messageId]);
            return;
        }

        // Skip if already enriched
        $meta = $message->content_attributes ?? [];
        if (! empty($meta['openai_enriched'])) {
            return;
        }

        try {
            $text = $message->content ?? '';
            if (trim($text) === '') {
                return;
            }

            // Simple summarization prompt (follow Rails porting behavior)
            $prompt = "Summarize the following customer message in one sentence:\n\n" . $text;

            $resp = $this->client->completions()->create([
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'prompt' => $prompt,
                'max_tokens' => 60,
            ]);

            $summary = $resp['choices'][0]['text'] ?? null;

            $meta['openai_enriched'] = [
                'summary' => $summary,
                'completed_at' => now()->toISOString(),
            ];

            $message->content_attributes = $meta;
            $message->save();
        } catch (\Throwable $e) {
            Log::error('OpenAIService::enrichMessage failed', ['error' => $e->getMessage(), 'message_id' => $messageId]);
            // Do not throw to avoid job storms; let job system handle retries if needed
        }
    }
}
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

    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Generate a reply suggestion based on conversation context
     */
    public function suggestReply(Conversation $conversation, ?string $additionalContext = null): array
    {
        $messages = $this->buildConversationMessages($conversation);

        $systemPrompt = "You are a helpful customer support assistant. Based on the conversation history, suggest a professional and helpful reply to the customer. Be concise, friendly, and solution-oriented.";

        if ($additionalContext) {
            $systemPrompt .= "\n\nAdditional context: " . $additionalContext;
        }

        return $this->chat([
            ['role' => 'system', 'content' => $systemPrompt],
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
            'empathetic' => 'empathetic and understanding',
            'concise' => 'brief and to the point while remaining polite',
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
     * Expand a brief response
     */
    public function expand(string $briefMessage): array
    {
        return $this->chat([
            [
                'role' => 'system',
                'content' => 'You are a writing assistant. Expand the given brief response into a more complete, professional customer support message. Add appropriate greetings, context, and closing remarks.',
            ],
            [
                'role' => 'user',
                'content' => "Please expand this brief response into a complete message:\n\n{$briefMessage}",
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
     * Translate a message
     */
    public function translate(string $message, string $targetLanguage): array
    {
        return $this->chat([
            [
                'role' => 'system',
                'content' => "You are a translator. Translate the given text to {$targetLanguage}. Maintain the tone and context of the original message. Return only the translated text.",
            ],
            [
                'role' => 'user',
                'content' => $message,
            ],
        ]);
    }

    /**
     * Detect sentiment of a message
     */
    public function detectSentiment(string $message): array
    {
        $result = $this->chat([
            [
                'role' => 'system',
                'content' => 'You are a sentiment analysis assistant. Analyze the given text and respond with a JSON object containing: "sentiment" (positive, negative, or neutral), "score" (a number from -1 to 1), and "explanation" (a brief explanation). Only respond with the JSON object.',
            ],
            [
                'role' => 'user',
                'content' => $message,
            ],
        ]);

        if ($result['success']) {
            try {
                $sentiment = json_decode($result['content'], true);
                return [
                    'success' => true,
                    'sentiment' => $sentiment['sentiment'] ?? 'neutral',
                    'score' => $sentiment['score'] ?? 0,
                    'explanation' => $sentiment['explanation'] ?? '',
                ];
            } catch (\Exception $e) {
                return [
                    'success' => true,
                    'sentiment' => 'neutral',
                    'score' => 0,
                    'raw_response' => $result['content'],
                ];
            }
        }

        return $result;
    }

    /**
     * Generate FAQ from conversation history
     */
    public function generateFAQ(array $conversations): array
    {
        $conversationSummaries = collect($conversations)
            ->map(function ($conversation) {
                return "Issue: " . ($conversation['subject'] ?? 'N/A') . "\nResolution: " . ($conversation['resolution'] ?? 'N/A');
            })
            ->join("\n\n---\n\n");

        return $this->chat([
            [
                'role' => 'system',
                'content' => 'You are a technical writer. Based on the provided conversation summaries, generate a list of frequently asked questions (FAQs) with answers. Format as a JSON array with "question" and "answer" fields.',
            ],
            [
                'role' => 'user',
                'content' => "Generate FAQs from these conversations:\n\n{$conversationSummaries}",
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
                $usage = $response->json('usage');

                return [
                    'success' => true,
                    'content' => trim($content),
                    'usage' => [
                        'prompt_tokens' => $usage['prompt_tokens'] ?? 0,
                        'completion_tokens' => $usage['completion_tokens'] ?? 0,
                        'total_tokens' => $usage['total_tokens'] ?? 0,
                    ],
                    'model' => $response->json('model'),
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
     * Generate embeddings for text
     */
    public function embeddings(string $text, string $model = 'text-embedding-ada-002'): array
    {
        if (!$this->apiKey) {
            return ['success' => false, 'error' => 'OpenAI API key not configured'];
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->post("{$this->apiUrl}/embeddings", [
                    'model' => $model,
                    'input' => $text,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'embedding' => $response->json('data.0.embedding'),
                    'usage' => $response->json('usage'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('error.message', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI embeddings request failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Transcribe audio file using OpenAI Whisper
     *
     * @param string $filePath
     * @param string $model
     * @return array
     */
    public function transcribeAudio(string $filePath, string $model = 'whisper-1'): array
    {
        if (!$this->apiKey) {
            return ['success' => false, 'error' => 'OpenAI API key not configured'];
        }

        try {
            $filename = basename($filePath);
            $request = Http::withToken($this->apiKey)
                ->timeout(120)
                ->attach('file', fopen($filePath, 'r'), $filename)
                ->asMultipart();

            $response = $request->post("{$this->apiUrl}/audio/transcriptions", [
                'model' => $model,
                'temperature' => 0.4,
            ]);

            if ($response->successful()) {
                return ['success' => true, 'text' => $response->json('text')];
            }

            return ['success' => false, 'error' => $response->body()];
        } catch (\Exception $e) {
            Log::error('OpenAI audio transcription failed', ['error' => $e->getMessage()]);
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

    /**
     * List available models
     */
    public function listModels(): array
    {
        if (!$this->apiKey) {
            return [];
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->get("{$this->apiUrl}/models");

            if ($response->successful()) {
                return collect($response->json('data', []))
                    ->filter(fn($m) => str_starts_with($m['id'], 'gpt-'))
                    ->map(fn($m) => [
                        'id' => $m['id'],
                        'created' => $m['created'],
                        'owned_by' => $m['owned_by'],
                    ])
                    ->values()
                    ->toArray();
            }

            return [];
        } catch (\Exception $e) {
            Log::error('OpenAI list models failed', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
