<?php

namespace App\Repositories\SuperAdmin;

use App\Models\Account;
use App\Repositories\BaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class AccountRepository extends BaseRepository
{
    public function __construct(Account $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated accounts with filters.
     */
    public function getPaginated(
        int $perPage = 20,
        ?string $search = null,
        ?string $status = null,
        bool $recent = false,
        bool $markedForDeletion = false
    ): LengthAwarePaginator {
        $query = $this->model->query();

        // Search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status) {
            $statusValue = $status === 'active' ? 0 : 1;
            $query->where('status', $statusValue);
        }

        // Recent filter (30 days)
        if ($recent) {
            $query->where('created_at', '>', now()->subDays(30));
        }

        // Marked for deletion filter
        if ($markedForDeletion) {
            $query->whereJsonLength('custom_attributes->marked_for_deletion_at', '>', 0);
        }

        return $query
            ->withCount(['users', 'inboxes', 'conversations'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get account with all counts and relationships.
     */
    public function getWithDetails(int $id): ?Account
    {
        return $this->model
            ->withCount(['users', 'inboxes', 'conversations', 'contacts'])
            ->with(['users' => function ($q) {
                $q->limit(10);
            }])
            ->find($id);
    }
}
