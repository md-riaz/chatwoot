<?php

namespace App\Actions\Reports;

use App\Repositories\Reports\HeatmapRepository;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Export Conversation Traffic Action
 * 
 * Generates CSV export of conversation heatmap data.
 * Rails parity: app/controllers/api/v2/accounts/reports_controller.rb#conversation_traffic
 */
class ExportConversationTrafficAction
{
    use AsAction;

    public function handle(
        int $accountId,
        int $daysBefore = 6,
        ?float $timezoneOffset = 0
    ): array {
        // Get timezone
        $timezone = $this->getTimezoneFromOffset($timezoneOffset);
        $timezoneToday = Carbon::now($timezone)->startOfDay();
        
        // Calculate date range
        $since = $timezoneToday->copy()->subDays($daysBefore);
        $until = $timezoneToday;

        // Get heatmap data
        $repository = app(HeatmapRepository::class);
        $data = $repository->getTimeseries(
            $accountId,
            'conversations_count',
            $since,
            $until,
            'hour',
            $timezone,
            ['type' => 'account']
        );

        // Transform data for CSV export
        $csvData = $this->transformDataForCsv($data, $timezone);

        return [
            'data' => $csvData,
            'timezone' => $timezone,
        ];
    }

    /**
     * Transform timeseries data into CSV format
     * 
     * Rails format:
     * [
     *   ['Start of the hour', '2024-01-01', '2024-01-02', ...],
     *   ['00:00', 0, 0, ...],
     *   ['01:00', 0, 0, ...],
     *   ...
     * ]
     */
    private function transformDataForCsv(array $data, string $timezone): array
    {
        // Group data by date and hour
        $grouped = [];
        foreach ($data as $item) {
            $date = Carbon::createFromTimestamp($item['timestamp'], $timezone);
            $dateStr = $date->toDateString();
            $hour = $date->hour;
            
            if (!isset($grouped[$hour])) {
                $grouped[$hour] = [];
            }
            $grouped[$hour][$dateStr] = $item['value'];
        }

        // Get unique dates in ascending order
        $dates = [];
        foreach ($data as $item) {
            $date = Carbon::createFromTimestamp($item['timestamp'], $timezone)->toDateString();
            if (!in_array($date, $dates)) {
                $dates[] = $date;
            }
        }
        sort($dates);

        // Build CSV array
        $result = [];
        
        // Header row
        $result[] = array_merge(['Start of the hour'], $dates);

        // Data rows (one per hour)
        for ($hour = 0; $hour < 24; $hour++) {
            $row = [sprintf('%02d:00', $hour)];
            
            foreach ($dates as $date) {
                $row[] = $grouped[$hour][$date] ?? 0;
            }
            
            $result[] = $row;
        }

        return $result;
    }

    /**
     * Get timezone string from offset
     */
    private function getTimezoneFromOffset(float $offset): string
    {
        $hours = (int) $offset;
        $minutes = abs(($offset - $hours) * 60);
        
        $sign = $offset >= 0 ? '+' : '-';
        return sprintf('%s%02d:%02d', $sign, abs($hours), $minutes);
    }
}
