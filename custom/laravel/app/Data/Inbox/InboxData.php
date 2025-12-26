<?php

namespace App\Data\Inbox;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class InboxData extends Data
{
    public function __construct(
        public int|Optional $id,
        #[Required]
        public int $account_id,
        #[Required, StringType, Max(255)]
        public string $name,
        #[Required, StringType]
        public string $channel_type,
        #[Nullable]
        public ?int $channel_id,
        public bool $enable_auto_assignment = true,
        public bool $greeting_enabled = false,
        #[Nullable]
        public ?string $greeting_message,
        public bool $enable_email_collect = true,
        public bool $csat_survey_enabled = false,
        public bool $allow_messages_after_resolved = true,
        public array|Optional $working_hours,
        #[StringType]
        public string $timezone = 'UTC',
        public bool $working_hours_enabled = false,
        #[Nullable]
        public ?string $out_of_office_message,
    ) {}

    public static function rules(): array
    {
        return [
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'name' => ['required', 'string', 'max:255'],
            'channel_type' => ['required', 'string'],
            'enable_auto_assignment' => ['boolean'],
            'greeting_enabled' => ['boolean'],
            'timezone' => ['string', 'timezone'],
        ];
    }
}
