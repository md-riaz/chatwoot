<?php

namespace App\Data\Translation;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class TranslationData extends Data
{
    public function __construct(
        #[Required]
        #[StringType]
        public string $text,

        #[Required]
        #[StringType]
        public string $target_language,

        #[StringType]
        public ?string $source_language = null,
    ) {}

    public static function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:5000'],
            'target_language' => ['required', 'string', 'size:2'],
            'source_language' => ['nullable', 'string', 'size:2'],
        ];
    }
}