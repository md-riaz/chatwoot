<?php

namespace App\Data\SuperAdmin;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class AccountData extends Data
{
    public function __construct(
        public int|Optional $id,
        #[Required, StringType, Max(255)]
        public string $name,
        #[Nullable, StringType, Max(10)]
        public ?string $locale = null,
        #[Nullable, StringType, Max(255)]
        public ?string $domain = null,
        #[Nullable, Email]
        public ?string $support_email = null,
        #[Nullable]
        public ?int $auto_resolve_duration = null,
        #[Nullable]
        public ?array $settings = null,
        #[Nullable]
        public ?array $limits = null,
        #[Nullable]
        public ?array $custom_attributes = null,
        #[Nullable]
        public ?array $internal_attributes = null,
        #[Nullable]
        public ?array $selected_feature_flags = null,
        #[Nullable]
        public ?array $manually_managed_features = null,
        #[Nullable]
        public ?array $enabled_features = null,
        #[Nullable]
        public ?array $all_features = null,
        #[Nullable]
        public ?array $account_users = null,
        #[In(['active', 'suspended'])]
        public string $status = 'active',
        public int|Optional $users_count = 0,
        public int|Optional $inboxes_count = 0,
        public int|Optional $conversations_count = 0,
        public int|Optional $contacts_count = 0,
        public string|Optional $created_at = '',
        public string|Optional $updated_at = '',
    ) {}

    /**
     * Override toArray to exclude null account_users from list responses
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        
        // Remove account_users if it's null (list view)
        if ($this->account_users === null) {
            unset($array['account_users']);
        }
        
        return $array;
    }
}
