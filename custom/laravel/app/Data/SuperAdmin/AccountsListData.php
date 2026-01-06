<?php

namespace App\Data\SuperAdmin;

use Spatie\LaravelData\Data;

class AccountsListData extends Data
{
    public function __construct(
        /** @var AccountData[] */
        public array $data,
        public AccountsListMetaData $meta,
    ) {}
}
