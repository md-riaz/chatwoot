<?php

namespace App\Data\SuperAdmin;

use Spatie\LaravelData\Data;

class AccountsListMetaData extends Data
{
    public function __construct(
        public int $total,
        public int $per_page,
        public int $current_page,
        public int $last_page,
    ) {}
}
