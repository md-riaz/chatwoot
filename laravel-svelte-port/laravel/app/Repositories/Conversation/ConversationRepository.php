<?php

namespace App\Repositories\Conversation;

use App\Models\Conversation;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ConversationRepository extends BaseRepository
{
    public function __construct(Conversation $model)
    {
        parent::__construct($model);
    }

    /**
     * Normalize a status value (string or int) to its integer constant.
     */
    private function normalizeStatus(mixed $status): int
    {
        if (is_int($status)) {
            return $status;
        }

        return match ((string) $status) {
            'open' => Conversation::STATUS_OPEN,
            'resolved' => Conversation::STATUS_RESOLVED,
            'pending' => Conversation::STATUS_PENDING,
            'snoozed' => Conversation::STATUS_SNOOZED,
            default => (int) $status,
        };
    }

    /**
     * Determine if a mixed value should be treated as true.
     */
    private function isTruthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes'], true);
        }

        return false;
    }

    /**
     * Normalize a label filter value into a list of non-empty titles.
     *
     * @return array<int, string>
     */
    private function normalizeLabels(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map(
                static fn ($item) => trim((string) $item),
                $value
            )));
        }

        if (is_string($value)) {
            return array_values(array_filter(array_map('trim', explode(',', $value))));
        }

        return [];
    }

    /**
     * Apply shared conversation listing/meta filters.
     */
    private function applyConversationFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $this->normalizeStatus($filters['status']));
        }

        if (isset($filters['inbox_id'])) {
            $query->where('inbox_id', $filters['inbox_id']);
        }

        if (isset($filters['team_id'])) {
            $query->where('team_id', $filters['team_id']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        $assigneeType = $filters['assignee_type'] ?? null;
        if ($assigneeType === 'me' && !empty($filters['current_user_id'])) {
            $query->where('assignee_id', $filters['current_user_id']);
        } elseif ($assigneeType === 'unassigned') {
            $query->whereNull('assignee_id');
        } elseif (isset($filters['assignee_id'])) {
            $query->where('assignee_id', $filters['assignee_id']);
        }

        if ($this->isTruthy($filters['unattended'] ?? false)) {
            $query->where('status', Conversation::STATUS_OPEN)->unattended();
        }

        if ($this->isTruthy($filters['mentioned'] ?? false) && !empty($filters['current_user_id'])) {
            $query->whereHas('mentions', function (Builder $mentionQuery) use ($filters) {
                $mentionQuery->where('user_id', $filters['current_user_id']);
            });
        }

        $labels = $this->normalizeLabels($filters['label'] ?? []);
        if (!empty($labels)) {
            $query->whereHas('labels', function (Builder $labelQuery) use ($labels) {
                $labelQuery->whereIn('title', $labels);
            });
        }

        return $query;
    }

    /**
     * Find conversations for a specific account with filters.
     */
    public function findForAccount(int $accountId, array $filters = []): LengthAwarePaginator
    {
        $query = $this->applyConversationFilters(
            $this->model->where('account_id', $accountId),
            $filters
        );

        return $query
            ->with(['contact', 'inbox', 'assignee', 'team'])
            ->orderByDesc('last_activity_at')
            ->paginate($filters['per_page'] ?? 25);
    }

    /**
     * Get conversation metadata/counts for an account.
     */
    public function getMetaForAccount(int $accountId, array $filters = []): array
    {
        $query = $this->applyConversationFilters(
            $this->model->where('account_id', $accountId),
            $filters
        );

        return [
            'all_count' => (clone $query)->count(),
            'open_count' => (clone $query)->where('status', \App\Models\Conversation::STATUS_OPEN)->count(),
            'resolved_count' => (clone $query)->where('status', \App\Models\Conversation::STATUS_RESOLVED)->count(),
            'pending_count' => (clone $query)->where('status', \App\Models\Conversation::STATUS_PENDING)->count(),
            'snoozed_count' => (clone $query)->where('status', \App\Models\Conversation::STATUS_SNOOZED)->count(),
            'unassigned_count' => (clone $query)->whereNull('assignee_id')->where('status', Conversation::STATUS_OPEN)->count(),
        ];
    }

    /**
     * Search conversations.
     */
    public function search(int $accountId, ?string $query, array $filters = []): LengthAwarePaginator
    {
        $builder = $this->model->where('account_id', $accountId);

        if ($query) {
            $builder->where(function ($q) use ($query) {
                $q->where('display_id', 'like', "%{$query}%")
                    ->orWhereHas('contact', function ($contactQuery) use ($query) {
                        $contactQuery->where('name', 'like', "%{$query}%")
                            ->orWhere('email', 'like', "%{$query}%")
                            ->orWhere('phone_number', 'like', "%{$query}%");
                    })
                    ->orWhereHas('messages', function ($messageQuery) use ($query) {
                        $messageQuery->where('content', 'like', "%{$query}%");
                    });
            });
        }

        if (isset($filters['status'])) {
            $builder->where('status', $this->normalizeStatus($filters['status']));
        }

        if (isset($filters['assignee_id'])) {
            $builder->where('assignee_id', $filters['assignee_id']);
        }

        if (isset($filters['inbox_id'])) {
            $builder->where('inbox_id', $filters['inbox_id']);
        }

        return $builder
            ->with(['contact', 'inbox', 'assignee'])
            ->orderByDesc('last_activity_at')
            ->paginate($filters['per_page'] ?? 25);
    }

    /**
     * Filter conversations with advanced payload.
     */
    public function filter(int $accountId, array $payload): array
    {
        $query = $this->model->where('account_id', $accountId);

        // Delegate the heavy-lifting to FilterService which mirrors Rails behavior
        $query = \App\Services\FilterService::applyFilters($query, $payload, $accountId);

        $conversations = $query
            ->with(['contact', 'inbox', 'assignee'])
            ->orderByDesc('last_activity_at')
            ->paginate(25);

        return [
            'conversations' => $conversations,
            'count' => $conversations->total(),
        ];
    }

    /**
     * Get unassigned conversations for an inbox.
     */
    public function getUnassignedForInbox(int $inboxId): Collection
    {
        return $this->model
            ->where('inbox_id', $inboxId)
            ->whereNull('assignee_id')
            ->where('status', Conversation::STATUS_OPEN)
            ->oldest()
            ->get();
    }

    /**
     * Get open conversations for an assignee.
     */
    public function getOpenForAssignee(int $assigneeId): Collection
    {
        return $this->model
            ->where('assignee_id', $assigneeId)
            ->where('status', Conversation::STATUS_OPEN)
            ->orderByDesc('last_activity_at')
            ->get();
    }

    /**
     * Get conversations that need auto-resolution.
     */
    public function getStaleConversations(int $hoursInactive = 48): Collection
    {
        return $this->model
            ->where('status', Conversation::STATUS_OPEN)
            ->where('last_activity_at', '<', now()->subHours($hoursInactive))
            ->get();
    }

    /**
     * Count open conversations by assignee.
     */
    public function countOpenByAssignee(int $accountId): array
    {
        return $this->model
            ->where('account_id', $accountId)
            ->where('status', Conversation::STATUS_OPEN)
            ->whereNotNull('assignee_id')
            ->selectRaw('assignee_id, count(*) as count')
            ->groupBy('assignee_id')
            ->pluck('count', 'assignee_id')
            ->toArray();
    }
}
