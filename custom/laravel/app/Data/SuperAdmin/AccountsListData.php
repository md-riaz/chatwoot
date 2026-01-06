<?php

namespace App\Data\SuperAdmin;

use Spatie\LaravelData\Data;

class AccountsListData extends Data
{
    public function __construct(
        public array $data,
        public AccountsListMetaData $meta,
    ) {}
}
