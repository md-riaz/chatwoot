<?php

namespace App\Data\Channels;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class InboundMessageData extends Data
{
    public function __construct(
        #[Required]
        public int $account_id,
        #[Required]
        public int $inbox_id,
        #[Required]
        #[StringType]
        public string $contact_identifier,
        public ?string $contact_source = null,
        public ?string $contact_name = null,
        public ?string $contact_email = null,
        public ?string $contact_phone = null,
        public ?string $provider_contact_id = null,
        public ?string $content = null,
        public int $content_type = \App\Models\Message::CONTENT_TEXT,
        public ?string $external_source_id = null,
        public array $attachments = [],
        public array $metadata = [],
        public ?int $conversation_id = null,
    ) {}
}
