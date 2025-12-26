<?php

namespace App\Repositories\Account;

use App\Models\Account;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AccountRepository extends BaseRepository
{
    public function __construct(Account $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all active accounts.
     */
    public function getActive(): Collection
    {
        return $this->model->active()->get();
    }

    /**
     * Find account by domain.
     */
    public function findByDomain(string $domain): ?Account
    {
        return $this->model->where('domain', $domain)->first();
    }

    /**
     * Get accounts with user counts.
     */
    public function getWithUserCounts(): LengthAwarePaginator
    {
        return $this->model
            ->withCount('users')
            ->paginate(25);
    }

    /**
     * Get account with all relationships loaded.
     */
    public function getWithRelations(int $id): ?Account
    {
        return $this->model
            ->with(['users', 'inboxes', 'teams', 'labels'])
            ->find($id);
    }
}
