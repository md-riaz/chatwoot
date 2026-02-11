<?php

namespace App\Http\Controllers\Api\V2;

use App\Actions\Reports\ExportConversationTrafficAction;
use App\Actions\Reports\GetHeatmapDataAction;
use App\Http\Controllers\Api\V1\Concerns\RequiresAccountAdmin;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\Reports\V2\ReportBuilder;
use App\Services\Reports\V2\Reports\BotMetricsBuilder;
use App\Services\Reports\V2\Reports\Conversations\MetricBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response as ResponseFacade;

class ReportsController extends Controller
{
    use RequiresAccountAdmin;

    /**
     * Get timeseries report data (for heatmaps).
     *
     * GET /api/v2/accounts/{account}/reports
     */
    public function index(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);

        // Validate parameters
        $request->validate([
            'metric' => 'required|string',
            'group_by' => 'required|string|in:hour,day,week,month,year',
            'since' => 'required|integer',
            'until' => 'required|integer',
            'type' => 'nullable|string',
            'id' => 'nullable|integer',
            'business_hours' => 'nullable|boolean',
            'timezone_offset' => 'nullable|numeric',
        ]);

        // Use Action to get heatmap data
        $data = GetHeatmapDataAction::run(
            accountId: $account->id,
            metric: $request->input('metric'),
            since: $request->integer('since'),
            until: $request->integer('until'),
            groupBy: $request->input('group_by', 'hour'),
            timezoneOffset: $request->input('timezone_offset', 0),
            type: $request->input('type'),
            id: $request->integer('id'),
            businessHours: $request->boolean('business_hours', false)
        );

        return response()->json($data);
    }

    /**
     * Get summary report data.
     */
    public function summary(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);

        $summary = $this->buildSummary($account, $request, 'summary');

        return response()->json($summary);
    }

    /**
     * Get bot summary report data.
     */
    public function botSummary(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);

        $summary = $this->buildSummary($account, $request, 'botSummary');

        return response()->json($summary);
    }

    /**
     * Get agents report as CSV.
     */
    public function agents(Request $request, Account $account): Response
    {
        $this->ensureAdmin($request, $account);

        $reportData = $this->generateAgentsReport($account, $request);

        return $this->generateCsv('agents_report', $reportData);
    }

    /**
     * Get inboxes report as CSV.
     */
    public function inboxes(Request $request, Account $account): Response
    {
        $this->ensureAdmin($request, $account);

        $reportData = $this->generateInboxesReport($account, $request);

        return $this->generateCsv('inboxes_report', $reportData);
    }

    /**
     * Get labels report as CSV.
     */
    public function labels(Request $request, Account $account): Response
    {
        $this->ensureAdmin($request, $account);

        $reportData = $this->generateLabelsReport($account, $request);

        return $this->generateCsv('labels_report', $reportData);
    }

    /**
     * Get teams report as CSV.
     */
    public function teams(Request $request, Account $account): Response
    {
        $this->ensureAdmin($request, $account);

        $reportData = $this->generateTeamsReport($account, $request);

        return $this->generateCsv('teams_report', $reportData);
    }

    /**
     * Get conversations summary report as CSV.
     */
    public function conversationsSummary(Request $request, Account $account): Response
    {
        $this->ensureAdmin($request, $account);

        $reportData = $this->generateConversationsSummaryReport($account, $request);

        return $this->generateCsv('conversations_summary_report', $reportData);
    }

    /**
     * Get conversation traffic report as CSV (heatmap export).
     *
     * GET /api/v2/accounts/{account}/reports/conversation_traffic
     */
    public function conversationTraffic(Request $request, Account $account): Response
    {
        $this->ensureAdmin($request, $account);

        // Validate parameters
        $request->validate([
            'days_before' => 'nullable|integer|min:0|max:365',
            'timezone_offset' => 'nullable|numeric',
        ]);

        // Use Action to generate CSV data
        $result = ExportConversationTrafficAction::run(
            accountId: $account->id,
            daysBefore: $request->integer('days_before', 6),
            timezoneOffset: $request->input('timezone_offset', 0)
        );

        // Generate CSV
        $csv = $this->generateHeatmapCsv($result['data'], $result['timezone']);

        return ResponseFacade::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="conversation_traffic_reports.csv"',
        ]);
    }

    /**
     * Generate CSV from heatmap data.
     */
    private function generateHeatmapCsv(array $reportData, string $timezone): string
    {
        $output = fopen('php://temp', 'r+');

        // First line: Timezone
        fputcsv($output, ['Timezone', $timezone]);

        // Write data rows
        foreach ($reportData as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Get conversation metrics.
     */
    public function conversations(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);

        if (empty($request->get('type'))) {
            return response()->json(['error' => 'Type parameter is required'], 422);
        }

        $params = $this->validateConversationParams($request);
        $builder = new ReportBuilder($account, $params);
        $metrics = $builder->conversationMetrics();

        return response()->json($metrics);
    }

    /**
     * Get bot metrics.
     */
    public function botMetrics(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);

        $params = $this->validateReportParams($request);
        $builder = new BotMetricsBuilder($account, $params);
        $metrics = $builder->metrics();

        return response()->json($metrics);
    }

    /**
     * Validate report parameters.
     */
    private function validateReportParams(Request $request): array
    {
        $request->validate([
            'type' => 'sometimes|string|in:account,agent,inbox,team,label',
            'id' => 'sometimes|integer',
            'group_by' => 'sometimes|string|in:day,week,month,year,hour',
            'business_hours' => 'sometimes|boolean',
            'metric' => 'sometimes|string',
            'since' => 'required|date',
            'until' => 'required|date|after_or_equal:since',
            'timezone_offset' => 'sometimes|numeric',
        ]);

        return [
            'type' => $request->get('type', 'account'),
            'id' => $request->get('id'),
            'group_by' => $request->get('group_by', 'day'),
            'business_hours' => $request->boolean('business_hours', false),
            'metric' => $request->get('metric'),
            'since' => $request->get('since'),
            'until' => $request->get('until'),
            'timezone_offset' => $request->get('timezone_offset', 0),
        ];
    }

    /**
     * Validate conversation parameters.
     */
    private function validateConversationParams(Request $request): array
    {
        $request->validate([
            'type' => 'required|string',
            'user_id' => 'sometimes|integer',
            'page' => 'sometimes|integer|min:1',
        ]);

        return [
            'type' => $request->get('type'),
            'user_id' => $request->get('user_id'),
            'page' => $request->get('page', 1),
        ];
    }

    /**
     * Build summary report with current and previous periods.
     */
    private function buildSummary(Account $account, Request $request, string $method): array
    {
        $range = $this->calculateDateRange($request);

        $currentParams = $this->getCommonParams($request) + [
            'since' => $range['current']['since'],
            'until' => $range['current']['until'],
            'timezone_offset' => $request->get('timezone_offset', 0),
        ];

        $previousParams = $this->getCommonParams($request) + [
            'since' => $range['previous']['since'],
            'until' => $range['previous']['until'],
            'timezone_offset' => $request->get('timezone_offset', 0),
        ];

        $currentBuilder = new MetricBuilder($account, $currentParams);
        $previousBuilder = new MetricBuilder($account, $previousParams);

        $currentSummary = $currentBuilder->$method();
        $previousSummary = $previousBuilder->$method();

        return array_merge($currentSummary, ['previous' => $previousSummary]);
    }

    /**
     * Get common parameters for reports.
     */
    private function getCommonParams(Request $request): array
    {
        return [
            'type' => $request->get('type', 'account'),
            'id' => $request->get('id'),
            'group_by' => $request->get('group_by'),
            'business_hours' => $request->boolean('business_hours', false),
        ];
    }

    /**
     * Calculate date range for current and previous periods.
     */
    private function calculateDateRange(Request $request): array
    {
        $since = strtotime($request->get('since'));
        $until = strtotime($request->get('until'));
        $duration = $until - $since;

        return [
            'current' => [
                'since' => $request->get('since'),
                'until' => $request->get('until'),
            ],
            'previous' => [
                'since' => date('Y-m-d H:i:s', $since - $duration),
                'until' => $request->get('since'),
            ],
        ];
    }

    /**
     * Generate agents report data.
     */
    private function generateAgentsReport(Account $account, Request $request): array
    {
        // This would use the AgentSummaryBuilder when implemented
        return [
            'headers' => ['Agent Name', 'Email', 'Conversations', 'Resolved', 'Avg Response Time'],
            'data' => [],
        ];
    }

    /**
     * Generate inboxes report data.
     */
    private function generateInboxesReport(Account $account, Request $request): array
    {
        // This would use the InboxSummaryBuilder when implemented
        return [
            'headers' => ['Inbox Name', 'Channel', 'Conversations', 'Resolved', 'Response Rate'],
            'data' => [],
        ];
    }

    /**
     * Generate labels report data.
     */
    private function generateLabelsReport(Account $account, Request $request): array
    {
        // This would use the LabelSummaryBuilder when implemented
        return [
            'headers' => ['Label', 'Conversations', 'Usage %'],
            'data' => [],
        ];
    }

    /**
     * Generate teams report data.
     */
    private function generateTeamsReport(Account $account, Request $request): array
    {
        // This would use the TeamSummaryBuilder when implemented
        return [
            'headers' => ['Team Name', 'Members', 'Conversations', 'Resolved', 'Avg Response Time'],
            'data' => [],
        ];
    }

    /**
     * Generate conversations summary report data.
     */
    private function generateConversationsSummaryReport(Account $account, Request $request): array
    {
        $since = $request->input('since', now()->subDays(30)->timestamp);
        $until = $request->input('until', now()->timestamp);

        $sinceAt = is_numeric($since) ? now()->createFromTimestamp((int) $since) : now()->parse($since);
        $untilAt = is_numeric($until) ? now()->createFromTimestamp((int) $until) : now()->parse($until);

        $summary = DB::table('conversations')
            ->where('account_id', $account->id)
            ->whereBetween('created_at', [$sinceAt, $untilAt])
            ->selectRaw('COUNT(*) as conversations_count')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as open_count', [\App\Models\Conversation::STATUS_OPEN])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as resolved_count', [\App\Models\Conversation::STATUS_RESOLVED])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_count', [\App\Models\Conversation::STATUS_PENDING])
            ->first();

        return [
            'headers' => ['conversations_count', 'open_count', 'resolved_count', 'pending_count'],
            'data' => [[
                $summary->conversations_count ?? 0,
                $summary->open_count ?? 0,
                $summary->resolved_count ?? 0,
                $summary->pending_count ?? 0,
            ]],
        ];
    }

    /**
     * Generate conversation traffic report data.
     */
    private function generateConversationTrafficReport(Account $account, Request $request): array
    {
        // This would use the HeatmapHelper when implemented
        return [
            'headers' => ['Hour', 'Day', 'Conversations', 'Messages'],
            'data' => [],
        ];
    }

    /**
     * Generate CSV response.
     */
    private function generateCsv(string $filename, array $reportData): Response
    {
        $csv = $this->arrayToCsv($reportData);

        return ResponseFacade::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}.csv",
        ]);
    }

    /**
     * Convert array data to CSV format.
     */
    private function arrayToCsv(array $data): string
    {
        $output = fopen('php://temp', 'r+');

        // Add headers
        if (isset($data['headers'])) {
            fputcsv($output, $data['headers']);
        }

        // Add data rows
        if (isset($data['data'])) {
            foreach ($data['data'] as $row) {
                fputcsv($output, $row);
            }
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
