<?php

namespace App\Data\SuperAdmin;

use Spatie\LaravelData\Data;

class DashboardData extends Data
{
    public function __construct(
        public string $accountsCount,
        public string $usersCount,
        public string $inboxesCount,
        public string $conversationsCount,
        public array $chartData,
    ) {}
}