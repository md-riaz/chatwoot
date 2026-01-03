<?php

namespace App\Services\Integrations;

use App\Models\Message;
use Google\Cloud\Translate\V3\TranslationServiceClient;
use Illuminate\Support\Facades\Log;

class GoogleTranslateService
{
    private ?TranslationServiceClient $client = null;
    private ?array $hook = null;

    public function __construct(?Message $message = null)
    {
        if ($message) {
            $this->initializeFromMessage($message);
        }
    }

    /**
     * Translate message content to target language
     */
    public function translate(Message $message, string $targetLanguage): ?string
    {
        if (!$this->client || !$this->hook) {
            return null;
        }

        $content = $message->content ?? '';
        if (empty($content)) {
            return null;
        }

        try {
            $response = $this->client->translateText([
                'contents' => [$content],
                'target_language_code' => $targetLanguage,
                'parent' => "projects/{$this->hook['project_id']}",
            ]);

            if (empty($response->getTranslations())) {
                return null;
            }

            return $response->getTranslations()[0]->getTranslatedText();
        } catch (\Exception $e) {
            Log::error('Translation failed', [
                'error' => $e->getMessage(),
                'message_id' => $message->id,
            ]);
            return null;
        }
    }

    /**
     * Initialize from message's account hooks
     */
    private function initializeFromMessage(Message $message): void
    {
        try {
            $hook = $this->getHookForAccount($message->account_id);
            
            if (!$hook) {
                return;
            }

            $this->hook = $hook;
            $this->client = new TranslationServiceClient([
                'credentials' => $hook['credentials'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to initialize Google Translate client', [
                'error' => $e->getMessage(),
                'message_id' => $message->id,
            ]);
        }
    }

    /**
     * Get hook configuration for account
     */
    private function getHookForAccount(int $accountId): ?array
    {
        $projectId = config('services.google_translate.project_id');
        $credentials = config('services.google_translate.credentials');

        if (!$projectId || !$credentials) {
            return null;
        }

        return [
            'project_id' => $projectId,
            'credentials' => $credentials,
        ];
    }
}