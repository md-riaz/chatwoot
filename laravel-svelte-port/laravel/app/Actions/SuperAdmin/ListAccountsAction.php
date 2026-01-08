<?php

namespace App\Actions\SuperAdmin;

use App\Actions\SuperAdmin\Traits\FormatsAccountData;
use App\Data\SuperAdmin\AccountData;
use App\Data\SuperAdmin\AccountsListData;
use App\Data\SuperAdmin\AccountsListMetaData;
use App\Repositories\SuperAdmin\AccountRepository;
use Lorisleiva\Actions\Concerns\AsAction;

class ListAccountsAction
{
    use AsAction;
    use FormatsAccountData;

    public function handle(
        int $perPage = 20,
        int $page = 1,
        ?string $search = null,
        ?string $status = null,
        bool $recent = false,
        bool $markedForDeletion = false
    ): AccountsListData {
        $accountRepository = app(AccountRepository::class);
        
        $paginated = $accountRepository->getPaginated(
            perPage: $perPage,
            search: $search,
            status: $status,
            recent: $recent,
            markedForDeletion: $markedForDeletion
        );

        // Convert to raw arrays (like DashboardData.chartData)
        $accounts = collect($paginated->items())->map(
            fn($account) => $this->formatAccountForList($account)->toArray()
        )->toArray();

        $meta = new AccountsListMetaData(
            total: $paginated->total(),
            per_page: $paginated->perPage(),
            current_page: $paginated->currentPage(),
            last_page: $paginated->lastPage(),
        );

        return new AccountsListData(data: $accounts, meta: $meta);
    }
}
