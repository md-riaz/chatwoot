<?php

namespace App\Actions\SuperAdmin;

use App\Data\SuperAdmin\DashboardData;
use App\Models\Account;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Inbox;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Carbon\Carbon;

class CalculateDashboardMetricsAction
{
    use AsAction;

    public function handle(): DashboardData
    {
        // Get basic counts with number formatting (matching Rails number_with_delimiter)
        $accountsCount = $this->formatNumber(Account::count());
        $usersCount = $this->formatNumber(User::count());
        $inboxesCount = $this->formatNumber(Inbox::count());
        $conversationsCount = $this->formatNumber(Conversation::count());

        // Get conversation chart data for last 30 days (matching Rails groupdate)
        $chartData = $this->getConversationChartData();

        return new DashboardData(
            accountsCount: $accountsCount,
            usersCount: $usersCount,
            inboxesCount: $inboxesCount,
            conversationsCount: $conversationsCount,
            chartData: $chartData
        );
    }

    /**
     * Format number with delimiters (matching Rails number_with_delimiter)
     * Rails uses comma as thousands separator by default
     */
    private function formatNumber(int $number): string
    {
        return number_format($number, 0, '.', ',');
    }

    /**
     * Get conversation data grouped by day for the last 30 days
     * Matches Rails: Conversation.unscoped.group_by_day(:created_at, range: 30.days.ago..2.seconds.ago).count.to_a
     */
    private function getConversationChartData(): array
    {
        // Match Rails date range exactly: 30.days.ago..2.seconds.ago
        $endDate = Carbon::now()->subSeconds(2);
        $startDate = Carbon::now()->subDays(30);

        // Get conversations grouped by date (using unscoped like Rails)
        $conversations = Conversation::withoutGlobalScopes()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Convert to array format matching Rails groupdate output: [[date, count], [date, count], ...]
        $chartData = [];
        
        // Create a complete date range with zero counts for missing dates (like Rails groupdate)
        $currentDate = $startDate->copy()->startOfDay();
        $conversationsByDate = $conversations->keyBy('date');
        
        while ($currentDate->lte($endDate->startOfDay())) {
            $dateString = $currentDate->format('Y-m-d');
            $count = $conversationsByDate->has($dateString) 
                ? (int) $conversationsByDate[$dateString]->count 
                : 0;
            
            // Rails groupdate returns [date_string, count] format
            $chartData[] = [$dateString, $count];
            $currentDate->addDay();
        }

        return $chartData;
    }
}