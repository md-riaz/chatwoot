<?php

namespace App\Actions\Filter;

use App\Models\Account;
use App\Models\User;
use App\Repositories\Filter\FilterRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class ApplyPermissionFiltersAction
{
    use AsAction;

    private FilterRepository $filterRepository;

    public function __construct()
    {
        $this->filterRepository = new FilterRepository();
    }

    /**
     * Apply permission-based filters to a query
     */
    public function handle(Builder $query, User $user, Account $account, string $type = 'conversation'): Builder
    {
        // Skip filtering for admin users
        if ($this->filterRepository->shouldSkipInboxFiltering($user, $account)) {
            return $query;
        }

        $accessibleInboxIds = $this->filterRepository->getAccessibleInboxIds($user, $account);

        if ($accessibleInboxIds->isEmpty()) {
            // User has no inbox access, return empty result
            return $query->whereRaw('1 = 0');
        }

        // Apply inbox filtering based on type
        switch ($type) {
            case 'conversation':
                return $query->whereIn('inbox_id', $accessibleInboxIds);

            case 'message':
                return $query->whereHas('conversation', function ($conversationQuery) use ($accessibleInboxIds) {
                    $conversationQuery->whereIn('inbox_id', $accessibleInboxIds);
                });

            case 'contact':
                return $query->whereHas('contactInboxes', function ($contactInboxQuery) use ($accessibleInboxIds) {
                    $contactInboxQuery->whereIn('inbox_id', $accessibleInboxIds);
                });

            default:
                return $query;
        }
    }

    /**
     * Get accessible inbox IDs for a user
     */
    public function getAccessibleInboxIds(User $user, Account $account): Collection
    {
        return $this->filterRepository->getAccessibleInboxIds($user, $account);
    }

    /**
     * Check if user should skip inbox filtering
     */
    public function shouldSkipInboxFiltering(User $user, Account $account): bool
    {
        return $this->filterRepository->shouldSkipInboxFiltering($user, $account);
    }

    /**
     * Check if user can access specific inbox
     */
    public function canAccessInbox(User $user, int $inboxId, Account $account): bool
    {
        return $this->filterRepository->canAccessInbox($user, $inboxId, $account);
    }

    /**
     * Apply team-based filtering
     */
    public function applyTeamFiltering(Builder $query, User $user, Account $account): Builder
    {
        $teamIds = $this->filterRepository->getUserTeamIds($user, $account);

        if ($teamIds->isEmpty()) {
            return $query;
        }

        return $query->where(function ($q) use ($teamIds, $user) {
            $q->whereIn('team_id', $teamIds)
              ->orWhere('assignee_id', $user->id)
              ->orWhereNull('team_id');
        });
    }
}