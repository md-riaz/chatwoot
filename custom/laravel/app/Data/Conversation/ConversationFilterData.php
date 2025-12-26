<?php

namespace App\Data\Conversation;

use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class ConversationFilterData extends Data
{
    public function __construct(
        #[Nullable, In([0, 1, 2, 3])]
        public int|Optional $status,
        #[Nullable]
        public int|Optional $assignee_id,
        #[Nullable]
        public int|Optional $inbox_id,
        #[Nullable]
        public int|Optional $team_id,
        #[Nullable, In([0, 1, 2, 3, 4])]
        public int|Optional $priority,
        #[Nullable]
        public array|Optional $labels,
        #[Nullable]
        public string|Optional $sort_by,
        #[Nullable, In(['asc', 'desc'])]
        public string|Optional $sort_order,
        public int $per_page = 25,
        public int $page = 1,
    ) {}
}
