<?php

namespace App\Actions\SuperAdmin;

use App\Actions\SuperAdmin\Traits\FormatsAccountData;
use App\Repositories\SuperAdmin\AccountRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\Concerns\AsAction;

class ListAccountsAction
{
    use AsAction;
    use FormatsAccountData;

    public function __construct(
        private AccountRepository $accountRepository
    ) {}

    public function handle(
        int $perPage = 20,
        int $page = 1,
        ?string $search = null,
        ?string $status = null,
        bool $recent = false,
        bool $markedForDeletion = false
    ): LengthAwarePaginator {
        $paginated = $this->accountRepository->getPaginated(
            perPage: $perPage,
            search: $search,
            status: $status,
            recent: $recent,
            markedForDeletion: $markedForDeletion
        );

        $paginated->setCollection(collect($paginated->items())->map(
            fn($account) => $this->formatAccountForList($account)->toArray()
        ));

        return $paginated;
    }
}
