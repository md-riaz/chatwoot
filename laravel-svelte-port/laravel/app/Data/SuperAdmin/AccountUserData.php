<?php

namespace App\Data\SuperAdmin;

use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class AccountUserData extends Data
{
    public function __construct(
        #[Required, Exists('users', 'id')]
        public int $user_id,
        
        #[Required, Exists('accounts', 'id')]
        public int $account_id,
        
        #[Required, In(['agent', 'admin', '1', '2'])]
        public string|int $role,
        
        #[In([0, 1])]
        public ?int $availability = 1,
        
        public ?array $settings = null,
    ) {}
}