<?php

namespace App\Repositories\Contact;

use App\Models\Contact;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ContactRepository extends BaseRepository
{
    private const RESULTS_PER_PAGE = 15;

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

        if (isset($filters['labels'])) {
            $query->whereHas('labels', function ($q) use ($filters) {
                $q->whereIn('title', (array) $filters['labels']);
            });
        }

        // Sorting
        $sortField = $filters['sort'] ?? 'last_activity_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($filters['per_page'] ?? self::RESULTS_PER_PAGE);
    }

    /**
     * Search contacts by query string.
     */
    public function search(int $accountId, string $query, array $filters = []): LengthAwarePaginator
    {
        $searchQuery = trim($query);

        return $this->model
            ->where('account_id', $accountId)
            ->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                    ->orWhere('email', 'like', "%{$searchQuery}%")
                    ->orWhere('phone_number', 'like', "%{$searchQuery}%")
                    ->orWhere('identifier', 'like', "%{$searchQuery}%")
                    ->orWhereRaw("additional_attributes->>'company_name' LIKE ?", ["%{$searchQuery}%"]);
            })
            ->orderByDesc('last_activity_at')
            ->paginate($filters['per_page'] ?? self::RESULTS_PER_PAGE);
    }

    /**
     * Filter contacts with advanced payload.
     */
    public function filter(int $accountId, array $payload, ?string $label = null): array
    {
        $query = $this->model->where('account_id', $accountId);

        if ($label) {
            $query->whereHas('labels', function ($q) use ($label) {
                $q->where('title', $label);
            });
        }

        foreach ($payload as $filter) {
            $attribute = $filter['attribute_key'] ?? null;
            $filterOperator = $filter['filter_operator'] ?? 'equal_to';
            $values = $filter['values'] ?? [];

            if (! $attribute || empty($values)) {
                continue;
            }

            switch ($attribute) {
                case 'name':
                case 'email':
                case 'phone_number':
                    if ($filterOperator === 'contains') {
                        $query->where($attribute, 'like', "%{$values[0]}%");
                    } elseif ($filterOperator === 'equal_to') {
                        $query->whereIn($attribute, $values);
                    } else {
                        $query->whereNotIn($attribute, $values);
                    }
                    break;
                case 'created_at':
                case 'last_activity_at':
                    if ($filterOperator === 'is_greater_than') {
                        $query->where($attribute, '>', $values[0]);
                    } elseif ($filterOperator === 'is_less_than') {
                        $query->where($attribute, '<', $values[0]);
                    }
                    break;
            }
        }

        $contacts = $query
            ->orderByDesc('last_activity_at')
            ->paginate(self::RESULTS_PER_PAGE);

        return [
            'contacts' => $contacts,
            'count' => $contacts->total(),
        ];
    }

    /**
     * Get active/online contacts.
     */
    public function getActiveContacts(int $accountId, array $filters = []): LengthAwarePaginator
    {
        return $this->model
            ->where('account_id', $accountId)
            ->where('last_activity_at', '>=', now()->subMinutes(5))
            ->orderByDesc('last_activity_at')
            ->paginate($filters['per_page'] ?? self::RESULTS_PER_PAGE);
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
