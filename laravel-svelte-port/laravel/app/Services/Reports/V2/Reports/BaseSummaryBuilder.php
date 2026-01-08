<?php

namespace App\Services\Reports\V2\Reports;

use App\Models\Account;
use App\Models\ReportingEvent;
use Carbon\Carbon;

abstract class BaseSummaryBuilder
{
    protected Account $account;
    protected array $params;

    public function __construct(Account $account, array $params)
    {
        $this->account = $account;
        $this->params = $params;
    }

    /**
     * Build the summary report.
     */
    abstract public function build(): array;

    /**
     * Get reporting events for the specified period.
     */
    protected function getReportingEvents(Carbon $since, Carbon $until)
    {
        return ReportingEvent::where('account_id', $this->account->id)
            ->whereBetween('created_at', [$since, $until])
            ->get();
    }

    /**
     * Calculate business hours if enabled.
     */
    protected function shouldUseBusinessHours(): bool
    {
        return $this->params['business_hours'] ?? false;
    }

    /**
     * Get date range from parameters.
     */
    protected function getDateRange(): array
    {
        return [
            'since' => Carbon::parse($this->params['since']),
            'until' => Carbon::parse($this->params['until']),
        ];
    }

    /**
     * Calculate metrics for a collection of events.
     */
    protected function calculateMetrics($events): array
    {
        return [
            'conversations_count' => $events->where('name', 'conversation_opened')->count(),
            'incoming_messages_count' => $events->where('name', 'message_created')
                ->where('value', 0)->count(),
            'outgoing_messages_count' => $events->where('name', 'message_created')
                ->where('value', 1)->count(),
            'resolutions_count' => $events->where('name', 'conversation_resolved')->count(),
            'avg_first_response_time' => $this->calculateAvgFirstResponseTime($events),
            'avg_resolution_time' => $this->calculateAvgResolutionTime($events),
            'reply_time' => $this->calculateAvgReplyTime($events),
        ];
    }

    /**
     * Calculate average first response time.
     */
    protected function calculateAvgFirstResponseTime($events): ?float
    {
        $firstResponseEvents = $events->where('name', 'first_response');
        
        if ($firstResponseEvents->isEmpty()) {
            return null;
        }
        
        $totalTime = $firstResponseEvents->sum('value');
        return $totalTime / $firstResponseEvents->count();
    }

    /**
     * Calculate average resolution time.
     */
    protected function calculateAvgResolutionTime($events): ?float
    {
        $resolutionEvents = $events->where('name', 'conversation_resolved');
        
        if ($resolutionEvents->isEmpty()) {
            return null;
        }
        
        $totalTime = $resolutionEvents->sum('value');
        return $totalTime / $resolutionEvents->count();
    }

    /**
     * Calculate average reply time.
     */
    protected function calculateAvgReplyTime($events): ?float
    {
        $replyTimeEvents = $events->where('name', 'reply_time');
        
        if ($replyTimeEvents->isEmpty()) {
            return null;
        }
        
        $totalTime = $replyTimeEvents->sum('value');
        return $totalTime / $replyTimeEvents->count();
    }
}