<?php

namespace App\Repositories\Inbox;

use App\Models\Inbox;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class InboxRepository extends BaseRepository
{
    public function __construct(Inbox $model)
    {
        parent::__construct($model);
    }

    /**
     * Find inboxes for a specific account.
     */
    public function findForAccount(int $accountId): Collection
    {
        return $this->model
            ->where('account_id', $accountId)
            ->with('channel')
            ->get();
    }

    /**
     * Get inboxes with conversation counts.
     */
    public function getWithConversationCounts(int $accountId): Collection
    {
        return $this->model
            ->where('account_id', $accountId)
            ->withCount(['conversations', 'conversations as open_conversations_count' => function ($query) {
                $query->where('status', 0); // STATUS_OPEN
            }])
            ->get();
    }

    /**
     * Get inboxes with auto-assignment enabled.
     */
    public function getWithAutoAssignment(int $accountId): Collection
    {
        return $this->model
            ->where('account_id', $accountId)
            ->where('enable_auto_assignment', true)
            ->get();
    }

    /**
     * Get inbox members (agents).
     */
    public function getMembers(int $inboxId): Collection
    {
        $inbox = $this->model->find($inboxId);
        if (! $inbox) {
            return new Collection;
        }

        return $inbox->members()->get();
    }

    /**
     * Add member to inbox.
     */
    public function addMember(int $inboxId, int $userId): bool
    {
        $inbox = $this->model->find($inboxId);
        if (! $inbox) {
            return false;
        }
        $inbox->members()->syncWithoutDetaching([$userId]);

        return true;
    }

    /**
     * Remove member from inbox.
     */
    public function removeMember(int $inboxId, int $userId): bool
    {
        $inbox = $this->model->find($inboxId);
        if (! $inbox) {
            return false;
        }
        $inbox->members()->detach($userId);

        return true;
    }
}
