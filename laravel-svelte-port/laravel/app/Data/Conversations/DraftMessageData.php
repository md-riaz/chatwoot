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
            'message' => ['required', 'string', 'max:10000', 'min:1'],
            'updated_at' => ['sometimes', 'nullable', 'date_format:Y-m-d\TH:i:s.u\Z'],
        ];
    }

    public static function messages(): array
    {
        return [
            'message.required' => 'Draft message content is required.',
            'message.min' => 'Draft message cannot be empty.',
            'message.max' => 'Draft message cannot exceed 10,000 characters.',
            'updated_at.date_format' => 'Updated at must be a valid ISO 8601 date format.',
        ];
    }
}