<?php

namespace App\Data\SuperAdmin;

use Spatie\LaravelData\Data;

class DashboardData extends Data
{
    public function __construct(
        public int $accounts_count,
        public int $users_count,
        public int $conversations_count,
        public int $messages_count,
        public int $contacts_count,
        public int $inboxes_count,
        public int $agent_bots_count,
        public int $active_accounts,
        public int $recent_signups,
        public array $account_status,
        public array $user_roles,
        public array $growth,
        public array $system_health,
        public array $recent_activity,
    ) {}
}