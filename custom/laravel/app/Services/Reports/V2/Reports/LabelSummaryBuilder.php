<?php

namespace App\Services\Reports\V2\Reports;

use Illuminate\Support\Facades\DB;

class LabelSummaryBuilder extends BaseSummaryBuilder
{
    /**
     * Build the label summary report.
     */
    public function build(): array
    {
        $dateRange = $this->getDateRange();
        
        // Get all labels for this account
        $labels = $this->account->labels()->get();
        
        $labelSummaries = [];
        $totalConversations = $this->getTotalConversationsInPeriod($dateRange);
        
        foreach ($labels as $label) {
            $labelUsage = $this->getLabelUsage($label, $dateRange);
            $usagePercentage = $totalConversations > 0 ? 
                ($labelUsage / $totalConversations) * 100 : 0;
            
            $labelSummaries[] = [
                'id' => $label->id,
                'title' => $label->title,
                'description' => $label->description,
                'color' => $label->color,
                'conversations_count' => $labelUsage,
                'usage_percentage' => round($usagePercentage, 2),
            ];
        }
        
        // Sort by usage count descending
        usort($labelSummaries, function ($a, $b) {
            return $b['conversations_count'] <=> $a['conversations_count'];
        });
        
        return [
            'labels' => $labelSummaries,
            'period' => [
                'since' => $dateRange['since']->toISOString(),
                'until' => $dateRange['until']->toISOString(),
            ],
            'business_hours' => $this->shouldUseBusinessHours(),
            'total_conversations' => $totalConversations,
        ];
    }

    /**
     * Get total conversations in the period.
     */
    private function getTotalConversationsInPeriod(array $dateRange): int
    {
        return $this->account->conversations()
            ->whereBetween('created_at', [$dateRange['since'], $dateRange['until']])
            ->count();
    }

    /**
     * Get label usage count in the period.
     */
    private function getLabelUsage($label, array $dateRange): int
    {
        // This assumes there's a conversation_labels pivot table
        // The exact implementation would depend on how labels are associated with conversations
        return DB::table('conversation_labels')
            ->join('conversations', 'conversation_labels.conversation_id', '=', 'conversations.id')
            ->where('conversation_labels.label_id', $label->id)
            ->where('conversations.account_id', $this->account->id)
            ->whereBetween('conversations.created_at', [$dateRange['since'], $dateRange['until']])
            ->count();
    }
}