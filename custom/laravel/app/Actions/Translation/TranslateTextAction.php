<?php

namespace App\Actions\Translation;

use App\Repositories\Translation\TranslationRepository;
use Lorisleiva\Actions\Concerns\AsAction;

class TranslateTextAction
{
    use AsAction;

    private TranslationRepository $translationRepository;

    public function __construct()
    {
        $this->translationRepository = new TranslationRepository();
    }

    /**
     * Translate given text to the target language
     */
    public function handle(string $text, string $targetLanguage, ?string $sourceLanguage = null): string
    {
        $provider = config('translation.default');

        if ($provider === 'libre') {
            return $this->translationRepository->translateWithLibre($text, $targetLanguage, $sourceLanguage);
        }

        // Add other providers (google, deepl) as needed.
        return $text;
    }
}