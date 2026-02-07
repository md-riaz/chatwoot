<?php

namespace App\Repositories\Reports;

use App\Models\Conversation;
use App\Repositories\BaseRepository;

/**
 * Live Reports Repository
 * 
 * Handles real-time conversation metrics queries.
 * Rails parity: app/controllers/api/v2/accounts/live_reports_controller.rb
 */
class LiveReportsRepository extends BaseRepository
{
    public function __construct(Conversation $model)
    {
        parent::__construct($model);
    }

    /**
     * Get account-level conversation metrics
     * 
     * @param int $accountId
     * @param int|null $teamId
     * @return array
     */
    public function getAccountMetrics(int $accountId, ?int $teamId = null): array
    {
        $query = $this->model->where('account_id', $accountId);

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        // Use the unattended scope from Conversation model
        return [
            'open' => (clone $query)->where('status', Conversation::STATUS_OPEN)->count(),
            'unattended' => (clone $query)->where('status', Conversation::STATUS_OPEN)
                ->unattended()->count(),
            'unassigned' => (clone $query)->where('status', Conversation::STATUS_OPEN)
                ->whereNull('assignee_id')->count(),
            'pending' => (clone $query)->where('status', Conversation::STATUS_PENDING)->count(),
        ];
    }

    /**
     * Get conversation metrics grouped by field (team_id or assignee_id)
     * 
     * @param int $accountId
     * @param string $groupBy 'team_id' or 'assignee_id'
     * @param int|null $teamId
     * @return array
     */
    public function getGroupedMetrics(int $accountId, string $groupBy, ?int $teamId = null): array
    {
        $query = $this->model->where('account_id', $accountId);

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        // Get open conversations grouped by field
        $openByGroup = (clone $query)
            ->where('status', Conversation::STATUS_OPEN)
            ->whereNotNull($groupBy)
            ->selectRaw("{$groupBy}, COUNT(*) as count")
            ->groupBy($groupBy)
            ->pluck('count', $groupBy)
            ->toArray();

        // Get unattended conversations grouped by field (use scope)
        $unattendedByGroup = (clone $query)
            ->where('status', Conversation::STATUS_OPEN)
            ->unattended()
            ->whereNotNull($groupBy)
            ->selectRaw("{$groupBy}, COUNT(*) as count")
            ->groupBy($groupBy)
            ->pluck('count', $groupBy)
            ->toArray();

        // Get unassigned conversations grouped by field
        $unassignedByGroup = (clone $query)
            ->where('status', Conversation::STATUS_OPEN)
            ->whereNull('assignee_id')
            ->whereNotNull($groupBy)
            ->selectRaw("{$groupBy}, COUNT(*) as count")
            ->groupBy($groupBy)
            ->pluck('count', $groupBy)
            ->toArray();

        // Build metrics array matching Rails format
        $metrics = [];
        foreach ($openByGroup as $groupId => $count) {
            $metrics[] = [
                'open' => $count,
                'unattended' => $unattendedByGroup[$groupId] ?? 0,
                'unassigned' => $unassignedByGroup[$groupId] ?? 0,
                $groupBy => $groupId, // Add group_id as a key (Rails pattern)
            ];
        }

        return $metrics;
    }
}
