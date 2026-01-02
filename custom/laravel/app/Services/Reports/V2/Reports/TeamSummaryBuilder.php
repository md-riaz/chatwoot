<?php

namespace App\Services\Reports\V2\Reports;

class TeamSummaryBuilder extends BaseSummaryBuilder
{
    /**
     * Build the team summary report.
     */
    public function build(): array
    {
        $dateRange = $this->getDateRange();
        $events = $this->getReportingEvents($dateRange['since'], $dateRange['until']);
        
        // Get all teams for this account
        $teams = $this->account->teams()->get();
        
        $teamSummaries = [];
        
        foreach ($teams as $team) {
            $teamEvents = $events->where('team_id', $team->id);
            $metrics = $this->calculateMetrics($teamEvents);
            
            $teamSummaries[] = [
                'id' => $team->id,
                'name' => $team->name,
                'description' => $team->description,
                'members_count' => $team->team_members()->count(),
                'conversations_count' => $metrics['conversations_count'],
                'incoming_messages_count' => $metrics['incoming_messages_count'],
                'outgoing_messages_count' => $metrics['outgoing_messages_count'],
                'resolutions_count' => $metrics['resolutions_count'],
                'avg_first_response_time' => $metrics['avg_first_response_time'],
                'avg_resolution_time' => $metrics['avg_resolution_time'],
                'reply_time' => $metrics['reply_time'],
            ];
        }
        
        // Sort by conversations count descending
        usort($teamSummaries, function ($a, $b) {
            return $b['conversations_count'] <=> $a['conversations_count'];
        });
        
        return [
            'teams' => $teamSummaries,
            'period' => [
                'since' => $dateRange['since']->toISOString(),
                'until' => $dateRange['until']->toISOString(),
            ],
            'business_hours' => $this->shouldUseBusinessHours(),
        ];
    }
}