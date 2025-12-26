<?php

namespace App\Data\Message;

use App\Models\Message;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class MessageData extends Data
{
    public function __construct(
        public int|Optional $id,
        #[Required]
        public int $account_id,
        #[Required]
        public int $conversation_id,
        #[Required]
        public int $inbox_id,
        #[Nullable]
        public ?int $sender_id,
        #[Nullable]
        public ?string $sender_type,
        #[In([Message::TYPE_INCOMING, Message::TYPE_OUTGOING, Message::TYPE_ACTIVITY, Message::TYPE_TEMPLATE])]
        public int $message_type = Message::TYPE_OUTGOING,
        #[Nullable]
        public ?string $content = null,
        #[In([Message::CONTENT_TEXT, Message::CONTENT_INPUT_TEXT, Message::CONTENT_INPUT_EMAIL, Message::CONTENT_INPUT_SELECT, Message::CONTENT_CARDS, Message::CONTENT_FORM, Message::CONTENT_ARTICLE])]
        public int $content_type = Message::CONTENT_TEXT,
        public array|Optional $content_attributes = [],
        public bool $private = false,
        #[Nullable]
        public ?string $external_source_id = null,
    ) {}

    public static function rules(): array
    {
        return [
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'conversation_id' => ['required', 'integer', 'exists:conversations,id'],
            'inbox_id' => ['required', 'integer', 'exists:inboxes,id'],
            'message_type' => ['integer', 'in:0,1,2,3'],
            'content' => ['nullable', 'string'],
            'private' => ['boolean'],
        ];
    }
}
