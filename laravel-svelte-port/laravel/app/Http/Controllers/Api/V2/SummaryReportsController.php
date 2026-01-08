<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\Concerns\RequiresAccountAdmin;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\Reports\V2\Reports\AgentSummaryBuilder;
use App\Services\Reports\V2\Reports\TeamSummaryBuilder;
use App\Services\Reports\V2\Reports\InboxSummaryBuilder;
use App\Services\Reports\V2\Reports\LabelSummaryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SummaryReportsController extends Controller
{
    use RequiresAccountAdmin;

    /**
     * Get agent summary report.
     */
    public function agent(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $params = $this->validateSummaryParams($request);
        
        return $this->renderReportWith(AgentSummaryBuilder::class, $account, $params);
    }

    /**
     * Get team summary report.
     */
    public function team(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $params = $this->validateSummaryParams($request);
        
        return $this->renderReportWith(TeamSummaryBuilder::class, $account, $params);
    }

    /**
     * Get inbox summary report.
     */
    public function inbox(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $params = $this->validateSummaryParams($request);
        
        return $this->renderReportWith(InboxSummaryBuilder::class, $account, $params);
    }

    /**
     * Get label summary report.
     */
    public function label(Request $request, Account $account): JsonResponse
    {
        $this->ensureAdmin($request, $account);
        
        $params = $this->validateSummaryParams($request);
        
        return $this->renderReportWith(LabelSummaryBuilder::class, $account, $params);
    }

    /**
     * Validate summary report parameters.
     */
    private function validateSummaryParams(Request $request): array
    {
        $request->validate([
            'since' => 'required|date',
            'until' => 'required|date|after_or_equal:since',
            'business_hours' => 'sometimes|boolean',
        ]);

        return [
            'since' => $request->get('since'),
            'until' => $request->get('until'),
            'business_hours' => $request->boolean('business_hours', false),
        ];
    }

    /**
     * Render report with the specified builder class.
     */
    private function renderReportWith(string $builderClass, Account $account, array $params): JsonResponse
    {
        try {
            $builder = new $builderClass($account, $params);
            $report = $builder->build();
            
            return response()->json($report);
        } catch (\Exception $e) {
            // Log the error and return a generic error response
            \Log::error("Summary report error: " . $e->getMessage(), [
                'builder_class' => $builderClass,
                'account_id' => $account->id,
                'params' => $params,
            ]);
            
            return response()->json([
                'error' => 'Failed to generate summary report',
                'message' => 'An error occurred while generating the report. Please try again.',
            ], 500);
        }
    }
}