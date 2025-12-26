<?php

namespace App\Data\Conversation;

use App\Models\Conversation;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class ConversationData extends Data
{
    public function __construct(
        public int|Optional $id,
        #[Required]
        public int $account_id,
        #[Required]
        public int $inbox_id,
        #[Required]
        public int $contact_id,
        #[Nullable]
        public ?int $contact_inbox_id,
        #[Nullable]
        public ?int $assignee_id,
        #[Nullable]
        public ?int $team_id,
        public int|Optional $display_id,
        #[In([Conversation::STATUS_OPEN, Conversation::STATUS_RESOLVED, Conversation::STATUS_PENDING, Conversation::STATUS_SNOOZED])]
        public int $status = Conversation::STATUS_OPEN,
        #[In([Conversation::PRIORITY_NONE, Conversation::PRIORITY_LOW, Conversation::PRIORITY_MEDIUM, Conversation::PRIORITY_HIGH, Conversation::PRIORITY_URGENT])]
        public int $priority = Conversation::PRIORITY_NONE,
        public array|Optional $custom_attributes,
        public ?string $snoozed_until,
    ) {}

    public static function rules(): array
    {
        return [
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'inbox_id' => ['required', 'integer', 'exists:inboxes,id'],
            'contact_id' => ['required', 'integer', 'exists:contacts,id'],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'status' => ['integer', 'in:0,1,2,3'],
            'priority' => ['integer', 'in:0,1,2,3,4'],
        ];
    }
}
