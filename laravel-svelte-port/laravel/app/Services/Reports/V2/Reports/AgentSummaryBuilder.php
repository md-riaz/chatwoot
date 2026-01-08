<?php

namespace App\Services\Reports\V2\Reports;

use App\Models\User;
use App\Enums\AccountUserRole;

class AgentSummaryBuilder extends BaseSummaryBuilder
{
    /**
     * Build the agent summary report.
     */
    public function build(): array
    {
        $dateRange = $this->getDateRange();
        $events = $this->getReportingEvents($dateRange['since'], $dateRange['until']);
        
        // Get all agents for this account
        $agents = $this->account->users()
            ->nonAdministrators()
            ->get();
        
        $agentSummaries = [];
        
        foreach ($agents as $agent) {
            $agentEvents = $events->where('user_id', $agent->id);
            $metrics = $this->calculateMetrics($agentEvents);
            
            $agentSummaries[] = [
                'id' => $agent->id,
                'name' => $agent->name,
                'email' => $agent->email,
                'thumbnail' => $agent->getAvatarUrl(),
                'availability_status' => $agent->availability_status ?? 'offline',
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
        usort($agentSummaries, function ($a, $b) {
            return $b['conversations_count'] <=> $a['conversations_count'];
        });
        
        return [
            'agents' => $agentSummaries,
            'period' => [
                'since' => $dateRange['since']->toISOString(),
                'until' => $dateRange['until']->toISOString(),
            ],
            'business_hours' => $this->shouldUseBusinessHours(),
        ];
    }
}