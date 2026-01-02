<?php

namespace App\Services\Reports\V2\Reports;

class InboxSummaryBuilder extends BaseSummaryBuilder
{
    /**
     * Build the inbox summary report.
     */
    public function build(): array
    {
        $dateRange = $this->getDateRange();
        $events = $this->getReportingEvents($dateRange['since'], $dateRange['until']);
        
        // Get all inboxes for this account
        $inboxes = $this->account->inboxes()->with('channel')->get();
        
        $inboxSummaries = [];
        
        foreach ($inboxes as $inbox) {
            $inboxEvents = $events->where('inbox_id', $inbox->id);
            $metrics = $this->calculateMetrics($inboxEvents);
            
            $inboxSummaries[] = [
                'id' => $inbox->id,
                'name' => $inbox->name,
                'channel_type' => $inbox->channel_type,
                'channel_name' => $this->getChannelName($inbox),
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
        usort($inboxSummaries, function ($a, $b) {
            return $b['conversations_count'] <=> $a['conversations_count'];
        });
        
        return [
            'inboxes' => $inboxSummaries,
            'period' => [
                'since' => $dateRange['since']->toISOString(),
                'until' => $dateRange['until']->toISOString(),
            ],
            'business_hours' => $this->shouldUseBusinessHours(),
        ];
    }

    /**
     * Get the channel name for display.
     */
    private function getChannelName($inbox): string
    {
        $channelTypeMap = [
            'Channel::WebWidget' => 'Website',
            'Channel::Api' => 'API',
            'Channel::Email' => 'Email',
            'Channel::FacebookPage' => 'Facebook',
            'Channel::TwitterProfile' => 'Twitter',
            'Channel::TelegramBot' => 'Telegram',
            'Channel::WhatsApp' => 'WhatsApp',
            'Channel::Sms' => 'SMS',
            'Channel::Line' => 'Line',
            'Channel::Instagram' => 'Instagram',
        ];
        
        return $channelTypeMap[$inbox->channel_type] ?? $inbox->channel_type;
    }
}