<?php

namespace App\Repositories\Contact;

use App\Models\Contact;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ContactRepository extends BaseRepository
{
    public function __construct(Contact $model)
    {
        parent::__construct($model);
    }

    /**
     * Find contacts for a specific account.
     */
    public function findForAccount(int $accountId, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->where('account_id', $accountId);

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        return $query
            ->orderByDesc('last_activity_at')
            ->paginate($filters['per_page'] ?? 25);
    }

    /**
     * Find contact by email within an account.
     */
    public function findByEmail(int $accountId, string $email): ?Contact
    {
        return $this->model
            ->where('account_id', $accountId)
            ->where('email', $email)
            ->first();
    }

    /**
     * Find contact by phone number within an account.
     */
    public function findByPhone(int $accountId, string $phoneNumber): ?Contact
    {
        return $this->model
            ->where('account_id', $accountId)
            ->where('phone_number', $phoneNumber)
            ->first();
    }

    /**
     * Find contact by identifier within an account.
     */
    public function findByIdentifier(int $accountId, string $identifier): ?Contact
    {
        return $this->model
            ->where('account_id', $accountId)
            ->where('identifier', $identifier)
            ->first();
    }

    /**
     * Get contacts with recent activity.
     */
    public function getRecentlyActive(int $accountId, int $days = 7): Collection
    {
        return $this->model
            ->where('account_id', $accountId)
            ->where('last_activity_at', '>=', now()->subDays($days))
            ->orderByDesc('last_activity_at')
            ->get();
    }
}
