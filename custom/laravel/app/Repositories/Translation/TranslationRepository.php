<?php

namespace App\Repositories\Translation;

use App\Repositories\BaseRepository;
use GuzzleHttp\Client;

class TranslationRepository extends BaseRepository
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client(['timeout' => 10]);
    }

    /**
     * Translate text using LibreTranslate
     */
    public function translateWithLibre(string $text, string $targetLanguage, ?string $sourceLanguage = null): string
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