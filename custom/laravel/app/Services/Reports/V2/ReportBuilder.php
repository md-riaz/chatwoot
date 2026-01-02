<?php

namespace App\Services\Reports\V2;

use App\Models\Account;
use App\Models\Conversation;
use App\Models\ReportingEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportBuilder
{
    protected Account $account;
    protected array $params;

    public function __construct(Account $account, array $params)
    {
        $this->account = $account;
        $this->params = $params;
    }

    /**
     * Generate timeseries data for reports.
     */
    public function timeseries(): array
    {
        $since = Carbon::parse($this->params['since']);
        $until = Carbon::parse($this->params['until']);
        $groupBy = $this->params['group_by'] ?? 'day';
        
        // Generate date range based on group_by
        $dateRange = $this->generateDateRange($since, $until, $groupBy);
        
        // Get reporting events for the period
        $events = $this->getReportingEvents($since, $until);
        
        // Group events by time period
        $groupedData = $this->groupEventsByTimePeriod($events, $dateRange, $groupBy);
        
        return [
            'data' => $groupedData,
            'period' => [
                'since' => $since->toISOString(),
                'until' => $until->toISOString(),
                'group_by' => $groupBy,
            ],
        ];
    }

    /**
     * Get conversation metrics.
     */
    public function conversationMetrics(): array
    {
        $type = $this->params['type'];
        $page = $this->params['page'] ?? 1;
        $perPage = 25;
        
        $query = $this->account->conversations();
        
        // Apply filters based on type
        if ($type === 'resolved') {
            $query->where('status', 'resolved');
        } elseif ($type === 'open') {
            $query->where('status', 'open');
        } elseif ($type === 'pending') {
            $query->where('status', 'pending');
        }
        
        // Apply user filter if specified
        if (isset($this->params['user_id'])) {
            $query->where('assignee_id', $this->params['user_id']);
        }
        
        $conversations = $query->with(['contact', 'assignee', 'inbox'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
        
        return [
            'data' => $conversations->items(),
            'meta' => [
                'current_page' => $conversations->currentPage(),
                'per_page' => $conversations->perPage(),
                'total' => $conversations->total(),
                'last_page' => $conversations->lastPage(),
            ],
        ];
    }

    /**
     * Generate date range based on grouping.
     */
    private function generateDateRange(Carbon $since, Carbon $until, string $groupBy): array
    {
        $range = [];
        $current = $since->copy();
        
        while ($current->lte($until)) {
            $range[] = $current->copy();
            
            switch ($groupBy) {
                case 'hour':
                    $current->addHour();
                    break;
                case 'day':
                    $current->addDay();
                    break;
                case 'week':
                    $current->addWeek();
                    break;
                case 'month':
                    $current->addMonth();
                    break;
                case 'year':
                    $current->addYear();
                    break;
            }
        }
        
        return $range;
    }

    /**
     * Get reporting events for the specified period.
     */
    private function getReportingEvents(Carbon $since, Carbon $until): Collection
    {
        $query = ReportingEvent::where('account_id', $this->account->id)
            ->whereBetween('created_at', [$since, $until]);
        
        // Apply type filter if specified
        if (isset($this->params['type']) && $this->params['type'] !== 'account') {
            $this->applyTypeFilter($query);
        }
        
        return $query->get();
    }

    /**
     * Apply type-specific filters to the query.
     */
    private function applyTypeFilter($query): void
    {
        $type = $this->params['type'];
        $id = $this->params['id'] ?? null;
        
        switch ($type) {
            case 'agent':
                if ($id) {
                    $query->where('user_id', $id);
                }
                break;
            case 'inbox':
                if ($id) {
                    $query->where('inbox_id', $id);
                }
                break;
            case 'team':
                if ($id) {
                    $query->where('team_id', $id);
                }
                break;
            case 'label':
                // This would need to be implemented based on how labels are tracked
                break;
        }
    }

    /**
     * Group events by time period.
     */
    private function groupEventsByTimePeriod(Collection $events, array $dateRange, string $groupBy): array
    {
        $groupedData = [];
        
        foreach ($dateRange as $date) {
            $periodKey = $this->getPeriodKey($date, $groupBy);
            
            // Filter events for this period
            $periodEvents = $events->filter(function ($event) use ($date, $groupBy) {
                $eventDate = Carbon::parse($event->created_at);
                return $this->isEventInPeriod($eventDate, $date, $groupBy);
            });
            
            $groupedData[] = [
                'timestamp' => $date->timestamp,
                'period' => $periodKey,
                'conversations_count' => $this->getConversationsCount($periodEvents),
                'incoming_messages_count' => $this->getIncomingMessagesCount($periodEvents),
                'outgoing_messages_count' => $this->getOutgoingMessagesCount($periodEvents),
                'resolutions_count' => $this->getResolutionsCount($periodEvents),
                'avg_first_response_time' => $this->getAvgFirstResponseTime($periodEvents),
                'avg_resolution_time' => $this->getAvgResolutionTime($periodEvents),
            ];
        }
        
        return $groupedData;
    }

    /**
     * Get period key for grouping.
     */
    private function getPeriodKey(Carbon $date, string $groupBy): string
    {
        switch ($groupBy) {
            case 'hour':
                return $date->format('Y-m-d H:00');
            case 'day':
                return $date->format('Y-m-d');
            case 'week':
                return $date->startOfWeek()->format('Y-m-d');
            case 'month':
                return $date->format('Y-m');
            case 'year':
                return $date->format('Y');
            default:
                return $date->format('Y-m-d');
        }
    }

    /**
     * Check if event is in the specified period.
     */
    private function isEventInPeriod(Carbon $eventDate, Carbon $periodDate, string $groupBy): bool
    {
        switch ($groupBy) {
            case 'hour':
                return $eventDate->format('Y-m-d H') === $periodDate->format('Y-m-d H');
            case 'day':
                return $eventDate->format('Y-m-d') === $periodDate->format('Y-m-d');
            case 'week':
                return $eventDate->weekOfYear === $periodDate->weekOfYear && 
                       $eventDate->year === $periodDate->year;
            case 'month':
                return $eventDate->format('Y-m') === $periodDate->format('Y-m');
            case 'year':
                return $eventDate->year === $periodDate->year;
            default:
                return $eventDate->format('Y-m-d') === $periodDate->format('Y-m-d');
        }
    }

    /**
     * Get conversations count from events.
     */
    private function getConversationsCount(Collection $events): int
    {
        return $events->where('name', 'conversation_opened')->count();
    }

    /**
     * Get incoming messages count from events.
     */
    private function getIncomingMessagesCount(Collection $events): int
    {
        return $events->where('name', 'message_created')
            ->where('value', 0) // incoming message type
            ->count();
    }

    /**
     * Get outgoing messages count from events.
     */
    private function getOutgoingMessagesCount(Collection $events): int
    {
        return $events->where('name', 'message_created')
            ->where('value', 1) // outgoing message type
            ->count();
    }

    /**
     * Get resolutions count from events.
     */
    private function getResolutionsCount(Collection $events): int
    {
        return $events->where('name', 'conversation_resolved')->count();
    }

    /**
     * Get average first response time from events.
     */
    private function getAvgFirstResponseTime(Collection $events): ?float
    {
        $firstResponseEvents = $events->where('name', 'first_response');
        
        if ($firstResponseEvents->isEmpty()) {
            return null;
        }
        
        $totalTime = $firstResponseEvents->sum('value');
        return $totalTime / $firstResponseEvents->count();
    }

    /**
     * Get average resolution time from events.
     */
    private function getAvgResolutionTime(Collection $events): ?float
    {
        $resolutionEvents = $events->where('name', 'conversation_resolved');
        
        if ($resolutionEvents->isEmpty()) {
            return null;
        }
        
        $totalTime = $resolutionEvents->sum('value');
        return $totalTime / $resolutionEvents->count();
    }
}