<?php

namespace App\Repositories\Reports;

use App\Models\Conversation;
use App\Models\Message;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

/**
 * Heatmap Repository
 * 
 * Handles heatmap data queries for reports.
 * Rails parity: app/builders/v2/report_builder.rb + app/helpers/api/v2/accounts/heatmap_helper.rb
 */
class HeatmapRepository extends BaseRepository
{
    public function __construct(Conversation $model)
    {
        parent::__construct($model);
    }

    /**
     * Get timeseries data for heatmaps
     * 
     * @param int $accountId
     * @param string $metric (conversations_count, resolutions_count, etc.)
     * @param Carbon $since
     * @param Carbon $until
     * @param string $groupBy (hour, day, week, month)
     * @param string $timezone
     * @param array $filters (type, id, business_hours)
     * @return array
     */
    public function getTimeseries(
        int $accountId,
        string $metric,
        Carbon $since,
        Carbon $until,
        string $groupBy,
        string $timezone,
        array $filters = []
    ): array {
        return match ($metric) {
            'conversations_count' => $this->getConversationsCount($accountId, $since, $until, $groupBy, $timezone, $filters),
            'resolutions_count' => $this->getResolutionsCount($accountId, $since, $until, $groupBy, $timezone, $filters),
            'incoming_messages_count' => $this->getIncomingMessagesCount($accountId, $since, $until, $groupBy, $timezone, $filters),
            'outgoing_messages_count' => $this->getOutgoingMessagesCount($accountId, $since, $until, $groupBy, $timezone, $filters),
            default => [],
        };
    }

    /**
     * Get conversations count grouped by time period
     */
    private function getConversationsCount(
        int $accountId,
        Carbon $since,
        Carbon $until,
        string $groupBy,
        string $timezone,
        array $filters
    ): array {
        $query = Conversation::where('account_id', $accountId)
            ->whereBetween('created_at', [$since, $until]);

        $query = $this->applyFilters($query, $filters);

        return $this->groupByPeriod($query, 'created_at', $groupBy, $timezone, $since, $until);
    }

    /**
     * Get resolutions count grouped by time period
     */
    private function getResolutionsCount(
        int $accountId,
        Carbon $since,
        Carbon $until,
        string $groupBy,
        string $timezone,
        array $filters
    ): array {
        $query = Conversation::where('account_id', $accountId)
            ->whereNotNull('resolved_at')
            ->whereBetween('resolved_at', [$since, $until]);

        $query = $this->applyFilters($query, $filters);

        return $this->groupByPeriod($query, 'resolved_at', $groupBy, $timezone, $since, $until);
    }

    /**
     * Get incoming messages count grouped by time period
     */
    private function getIncomingMessagesCount(
        int $accountId,
        Carbon $since,
        Carbon $until,
        string $groupBy,
        string $timezone,
        array $filters
    ): array {
        $query = Message::where('account_id', $accountId)
            ->where('message_type', Message::MESSAGE_TYPE_INCOMING)
            ->whereBetween('created_at', [$since, $until]);

        $query = $this->applyFilters($query, $filters);

        return $this->groupByPeriod($query, 'created_at', $groupBy, $timezone, $since, $until);
    }

    /**
     * Get outgoing messages count grouped by time period
     */
    private function getOutgoingMessagesCount(
        int $accountId,
        Carbon $since,
        Carbon $until,
        string $groupBy,
        string $timezone,
        array $filters
    ): array {
        $query = Message::where('account_id', $accountId)
            ->where('message_type', Message::MESSAGE_TYPE_OUTGOING)
            ->whereBetween('created_at', [$since, $until]);

        $query = $this->applyFilters($query, $filters);

        return $this->groupByPeriod($query, 'created_at', $groupBy, $timezone, $since, $until);
    }

    /**
     * Apply type-based filters (inbox, team, user, label)
     */
    private function applyFilters($query, array $filters)
    {
        $type = $filters['type'] ?? null;
        $id = $filters['id'] ?? null;

        if ($type && $id) {
            match ($type) {
                'inbox' => $query->where('inbox_id', $id),
                'team' => $query->where('team_id', $id),
                'user', 'assignee' => $query->where('assignee_id', $id),
                'label' => $query->whereHas('labels', fn($q) => $q->where('labels.id', $id)),
                default => $query,
            };
        }

        return $query;
    }

    /**
     * Group query results by time period
     * 
     * Rails uses groupdate gem, we implement similar functionality
     */
    private function groupByPeriod(
        $query,
        string $dateColumn,
        string $groupBy,
        string $timezone,
        Carbon $since,
        Carbon $until
    ): array {
        // Get date format for MySQL
        $format = $this->getDateFormat($groupBy);
        
        // Group and count with timezone conversion
        $results = $query
            ->selectRaw("DATE_FORMAT(CONVERT_TZ({$dateColumn}, '+00:00', ?), ?) as period, COUNT(*) as value", [
                $timezone,
                $format
            ])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Convert to timestamp => value array
        $data = [];
        foreach ($results as $result) {
            $timestamp = $this->periodToTimestamp($result->period, $groupBy, $timezone);
            $data[$timestamp] = (int) $result->value;
        }

        // Fill missing periods with zeros
        return $this->fillMissingPeriods($data, $groupBy, $since, $until, $timezone);
    }

    /**
     * Get MySQL DATE_FORMAT string for group_by period
     */
    private function getDateFormat(string $groupBy): string
    {
        return match ($groupBy) {
            'hour' => '%Y-%m-%d %H:00:00',
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m-%d',
        };
    }

    /**
     * Convert period string to Unix timestamp
     */
    private function periodToTimestamp(string $period, string $groupBy, string $timezone): int
    {
        return match ($groupBy) {
            'hour' => Carbon::parse($period, $timezone)->timestamp,
            'day' => Carbon::parse($period, $timezone)->startOfDay()->timestamp,
            'week' => Carbon::parse($period . '-1', $timezone)->startOfWeek()->timestamp,
            'month' => Carbon::parse($period . '-01', $timezone)->startOfMonth()->timestamp,
            'year' => Carbon::parse($period . '-01-01', $timezone)->startOfYear()->timestamp,
            default => Carbon::parse($period, $timezone)->timestamp,
        };
    }

    /**
     * Fill missing periods with zero values
     */
    private function fillMissingPeriods(
        array $data,
        string $groupBy,
        Carbon $since,
        Carbon $until,
        string $timezone
    ): array {
        // Create period iterator
        $interval = match ($groupBy) {
            'hour' => '1 hour',
            'day' => '1 day',
            'week' => '1 week',
            'month' => '1 month',
            'year' => '1 year',
            default => '1 day',
        };

        $period = CarbonPeriod::create($since, $interval, $until);
        
        // Fill all periods
        $filled = [];
        foreach ($period as $date) {
            $timestamp = $date->timestamp;
            $filled[] = [
                'timestamp' => $timestamp,
                'value' => $data[$timestamp] ?? 0,
            ];
        }
        
        return $filled;
    }
}
