<?php

namespace App\Services\Reports\V2\Reports;

class LabelSummaryBuilder extends BaseSummaryBuilder
{
    /**
     * Build the label summary report.
     */
    public function build(): array
    {
        $dateRange = $this->getDateRange();

        $labels = $this->account->labels()->get();
        $totalConversations = $this->getTotalConversationsInPeriod($dateRange);

        $labelSummaries = [];

        $summaryByLabelId = (new \App\Services\Reports\V2\Reports\Conversations\MetricBuilder($this->account, [
            'since' => $dateRange['since'],
            'until' => $dateRange['until'],
            'type' => 'label',
        ]))->summaryByIds($labels->pluck('id')->all());

        foreach ($labels as $label) {
            $summary = $summaryByLabelId[$label->id] ?? [];

            $usagePercentage = $totalConversations > 0
                ? (($summary['conversations_count'] ?? 0) / $totalConversations) * 100
                : 0;

            $labelSummaries[] = [
                'id' => $label->id,
                'title' => $label->title,
                'description' => $label->description,
                'color' => $label->color,
                'conversations_count' => $summary['conversations_count'] ?? 0,
                'incoming_messages_count' => $summary['incoming_messages_count'] ?? 0,
                'outgoing_messages_count' => $summary['outgoing_messages_count'] ?? 0,
                'resolutions_count' => $summary['resolutions_count'] ?? 0,
                'avg_first_response_time' => $summary['avg_first_response_time'] ?? null,
                'avg_resolution_time' => $summary['avg_resolution_time'] ?? null,
                'reply_time' => $summary['reply_time'] ?? null,
                'usage_percentage' => round($usagePercentage, 2),
            ];
        }

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
}
