<?php

namespace App\Services\Reports\V2\Reports\Conversations;

use App\Models\Account;
use App\Models\ReportingEvent;
use Carbon\Carbon;

class MetricBuilder
{
    protected Account $account;
    protected array $params;

    public function __construct(Account $account, array $params)
    {
        $this->account = $account;
        $this->params = $params;
    }

    /**
     * Generate summary metrics.
     */
    public function summary(): array
    {
        $since = Carbon::parse($this->params['since']);
        $until = Carbon::parse($this->params['until']);
        
        $events = $this->getReportingEvents($since, $until);
        
        return [
            'conversations_count' => $this->getConversationsCount($events),
            'incoming_messages_count' => $this->getIncomingMessagesCount($events),
            'outgoing_messages_count' => $this->getOutgoingMessagesCount($events),
            'resolutions_count' => $this->getResolutionsCount($events),
            'avg_first_response_time' => $this->getAvgFirstResponseTime($events),
            'avg_resolution_time' => $this->getAvgResolutionTime($events),
            'reply_time' => $this->getAvgReplyTime($events),
        ];
    }

    /**
     * Generate bot summary metrics.
     */
    public function botSummary(): array
    {
        $since = Carbon::parse($this->params['since']);
        $until = Carbon::parse($this->params['until']);
        
        $events = $this->getReportingEvents($since, $until);
        
        return [
            'bot_resolutions_count' => $this->getBotResolutionsCount($events),
            'bot_handoffs_count' => $this->getBotHandoffsCount($events),
            'bot_resolution_rate' => $this->getBotResolutionRate($events),
            'avg_bot_resolution_time' => $this->getAvgBotResolutionTime($events),
        ];
    }

    /**
     * Get reporting events for the specified period.
     */
    private function getReportingEvents(Carbon $since, Carbon $until)
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
     * Get conversations count from events.
     */
    private function getConversationsCount($events): int
    {
        return $events->where('name', 'conversation_opened')->count();
    }

    /**
     * Get incoming messages count from events.
     */
    private function getIncomingMessagesCount($events): int
    {
        return $events->where('name', 'message_created')
            ->where('value', 0) // incoming message type
            ->count();
    }

    /**
     * Get outgoing messages count from events.
     */
    private function getOutgoingMessagesCount($events): int
    {
        return $events->where('name', 'message_created')
            ->where('value', 1) // outgoing message type
            ->count();
    }

    /**
     * Get resolutions count from events.
     */
    private function getResolutionsCount($events): int
    {
        return $events->where('name', 'conversation_resolved')->count();
    }

    /**
     * Get average first response time from events.
     */
    private function getAvgFirstResponseTime($events): ?float
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
    private function getAvgResolutionTime($events): ?float
    {
        $resolutionEvents = $events->where('name', 'conversation_resolved');
        
        if ($resolutionEvents->isEmpty()) {
            return null;
        }
        
        $totalTime = $resolutionEvents->sum('value');
        return $totalTime / $resolutionEvents->count();
    }

    /**
     * Get average reply time from events.
     */
    private function getAvgReplyTime($events): ?float
    {
        $replyTimeEvents = $events->where('name', 'reply_time');
        
        if ($replyTimeEvents->isEmpty()) {
            return null;
        }
        
        $totalTime = $replyTimeEvents->sum('value');
        return $totalTime / $replyTimeEvents->count();
    }

    /**
     * Get bot resolutions count from events.
     */
    private function getBotResolutionsCount($events): int
    {
        return $events->where('name', 'conversation_bot_resolved')->count();
    }

    /**
     * Get bot handoffs count from events.
     */
    private function getBotHandoffsCount($events): int
    {
        return $events->where('name', 'conversation_bot_handoff')->count();
    }

    /**
     * Get bot resolution rate.
     */
    private function getBotResolutionRate($events): float
    {
        $botResolutions = $this->getBotResolutionsCount($events);
        $botHandoffs = $this->getBotHandoffsCount($events);
        $totalBotInteractions = $botResolutions + $botHandoffs;
        
        if ($totalBotInteractions === 0) {
            return 0.0;
        }
        
        return ($botResolutions / $totalBotInteractions) * 100;
    }

    /**
     * Get average bot resolution time from events.
     */
    private function getAvgBotResolutionTime($events): ?float
    {
        $botResolutionEvents = $events->where('name', 'conversation_bot_resolved');
        
        if ($botResolutionEvents->isEmpty()) {
            return null;
        }
        
        $totalTime = $botResolutionEvents->sum('value');
        return $totalTime / $botResolutionEvents->count();
    }
}