<?php

namespace App\Data\Conversation;

use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class ParticipantData extends Data
{
    public function __construct(
        #[Required]
        #[ArrayType]
        /** @var int[] */
        public array $user_ids,
    ) {}

    public static function rules(): array
    {
        return [
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ];
    }
}