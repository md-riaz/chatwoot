<?php

namespace App\Services\Reports\V2\Reports;

use App\Models\Account;
use App\Models\ReportingEvent;
use Carbon\Carbon;

class BotMetricsBuilder
{
    protected Account $account;
    protected array $params;

    public function __construct(Account $account, array $params)
    {
        $this->account = $account;
        $this->params = $params;
    }

    /**
     * Generate bot metrics.
     */
    public function metrics(): array
    {
        $since = Carbon::parse($this->params['since']);
        $until = Carbon::parse($this->params['until']);
        
        $events = $this->getReportingEvents($since, $until);
        
        return [
            'bot_resolutions' => $this->getBotResolutions($events),
            'bot_handoffs' => $this->getBotHandoffs($events),
            'bot_resolution_rate' => $this->getBotResolutionRate($events),
            'avg_bot_resolution_time' => $this->getAvgBotResolutionTime($events),
            'bot_conversations' => $this->getBotConversations($events),
            'total_bot_interactions' => $this->getTotalBotInteractions($events),
        ];
    }

    /**
     * Get reporting events for the specified period.
     */
    private function getReportingEvents(Carbon $since, Carbon $until)
    {
        return ReportingEvent::where('account_id', $this->account->id)
            ->whereBetween('created_at', [$since, $until])
            ->whereIn('name', [
                'conversation_bot_resolved',
                'conversation_bot_handoff',
                'conversation_opened'
            ])
            ->get();
    }

    /**
     * Get bot resolutions count.
     */
    private function getBotResolutions($events): int
    {
        return $events->where('name', 'conversation_bot_resolved')->count();
    }

    /**
     * Get bot handoffs count.
     */
    private function getBotHandoffs($events): int
    {
        return $events->where('name', 'conversation_bot_handoff')->count();
    }

    /**
     * Get bot resolution rate.
     */
    private function getBotResolutionRate($events): float
    {
        $botResolutions = $this->getBotResolutions($events);
        $botHandoffs = $this->getBotHandoffs($events);
        $totalBotInteractions = $botResolutions + $botHandoffs;
        
        if ($totalBotInteractions === 0) {
            return 0.0;
        }
        
        return ($botResolutions / $totalBotInteractions) * 100;
    }

    /**
     * Get average bot resolution time.
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

    /**
     * Get bot conversations count.
     */
    private function getBotConversations($events): int
    {
        // This would need to be implemented based on how bot conversations are tracked
        // For now, return the sum of bot resolutions and handoffs
        return $this->getBotResolutions($events) + $this->getBotHandoffs($events);
    }

    /**
     * Get total bot interactions.
     */
    private function getTotalBotInteractions($events): int
    {
        return $this->getBotResolutions($events) + $this->getBotHandoffs($events);
    }
}