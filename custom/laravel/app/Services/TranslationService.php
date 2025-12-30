<?php

namespace App\Services;

use GuzzleHttp\Client;

/**
 * TranslationService with a pluggable provider.
 * Default provider: LibreTranslate (configurable via env LIBRE_TRANSLATE_URL and LIBRE_TRANSLATE_API_KEY).
 */
class TranslationService
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client(['timeout' => 10]);
    }

    /**
     * Translate given text to the target language.
     * Falls back to returning the original text on error.
     *
     * @param string $text
     * @param string $targetLanguage
     * @param string|null $sourceLanguage
     * @return string
     */
    public function translate(string $text, string $targetLanguage, ?string $sourceLanguage = null): string
    {
        $provider = env('TRANSLATION_PROVIDER', 'libre');

        if ($provider === 'libre') {
            return $this->translateWithLibre($text, $targetLanguage, $sourceLanguage);
        }

        // Add other providers (google, deepl) as needed.

        return $text;
    }

    protected function translateWithLibre(string $text, string $targetLanguage, ?string $sourceLanguage = null): string
    {
        $url = env('LIBRE_TRANSLATE_URL', 'https://libretranslate.com/translate');
        $apiKey = env('LIBRE_TRANSLATE_API_KEY');

        try {
            $params = [
                'q' => $text,
                'source' => $sourceLanguage ?? 'auto',
                'target' => $targetLanguage,
                'format' => 'text',
            ];

            if ($apiKey) {
                $params['api_key'] = $apiKey;
            }

            $resp = $this->http->post($url, ['form_params' => $params]);
            $body = json_decode((string) $resp->getBody(), true);

            if (is_array($body) && isset($body['translatedText'])) {
                return $body['translatedText'];
            }
        } catch (\Exception $e) {
            // swallow and fallback
        }

        return $text;
    }
}
