<?php

namespace App\Data\Search;

use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class SearchQueryData extends Data
{
    public function __construct(
        #[Required]
        #[StringType]
        public string $query,

        #[Required]
        #[StringType]
        #[In(['Message', 'Conversation', 'Contact', 'Article', 'all'])]
        public string $search_type,

        #[IntegerType]
        #[Min(1)]
        public int $limit = 15,

        #[IntegerType]
        #[Min(0)]
        public int $offset = 0,
    ) {}

    public static function rules(): array
    {
        return [
            'query' => ['required', 'string', 'min:1', 'max:255'],
            'search_type' => ['required', 'string', 'in:Message,Conversation,Contact,Article,all'],
            'limit' => ['integer', 'min:1', 'max:100'],
            'offset' => ['integer', 'min:0'],
        ];
    }
}