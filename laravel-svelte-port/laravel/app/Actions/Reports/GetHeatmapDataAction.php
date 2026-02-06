<?php

namespace App\Actions\Reports;

use App\Repositories\Reports\HeatmapRepository;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Get Heatmap Data Action
 * 
 * Returns timeseries data for heatmap visualization.
 * Rails parity: app/controllers/api/v2/accounts/reports_controller.rb#index
 */
class GetHeatmapDataAction
{
    use AsAction;

    public function handle(
        int $accountId,
        string $metric,
        int $since,
        int $until,
        string $groupBy = 'hour',
        ?float $timezoneOffset = 0,
        ?string $type = null,
        ?int $id = null,
        bool $businessHours = false
    ): array {
        // Convert timestamps to Carbon instances
        $sinceDate = Carbon::createFromTimestamp($since);
        $untilDate = Carbon::createFromTimestamp($until);
        
        // Get timezone from offset
        $timezone = $this->getTimezoneFromOffset($timezoneOffset);
        
        // Build filters
        $filters = [];
        if ($type) {
            $filters['type'] = $type;
        }
        if ($id) {
            $filters['id'] = $id;
        }
        if ($businessHours) {
            $filters['business_hours'] = true;
        }

        // Get timeseries data
        $repository = app(HeatmapRepository::class);
        
        return $repository->getTimeseries(
            $accountId,
            $metric,
            $sinceDate,
            $untilDate,
            $groupBy,
            $timezone,
            $filters
        );
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
