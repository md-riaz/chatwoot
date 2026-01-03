<?php

namespace App\Data\Voice;

use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class CallData extends Data
{
    public function __construct(
        #[Required]
        public int $account_id,
        #[Required]
        public int $inbox_id,
        #[Required]
        public int $contact_id,
        #[Required]
        public int $user_id,
        #[Nullable]
        public ?string $call_sid = null,
        #[Nullable]
        public ?string $conference_sid = null,
        public string $direction = 'outbound',
        public string $status = 'ringing',
        public array|Optional $metadata = [],
    ) {}

    public static function rules(): array
    {
        return [
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'inbox_id' => ['required', 'integer', 'exists:inboxes,id'],
            'contact_id' => ['required', 'integer', 'exists:contacts,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'direction' => ['string', 'in:inbound,outbound'],
            'status' => ['string', 'in:ringing,in-progress,completed,no-answer,failed'],
        ];
    }
}