<?php

namespace App\Data\Conversations;

use Spatie\LaravelData\Data;

class DraftMessageData extends Data
{
    public function __construct(
        public string $message,
        public ?string $updated_at = null,
    ) {}

    public static function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:10000'],
            'updated_at' => ['sometimes', 'date'],
        ];
    }

    public static function messages(): array
    {
        return [
            'message.required' => 'Draft message content is required.',
            'message.max' => 'Draft message cannot exceed 10,000 characters.',
            'updated_at.date' => 'Updated at must be a valid date.',
        ];
    }
}