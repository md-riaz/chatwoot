<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Actions\SuperAdmin\CalculateDashboardMetricsAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Show super admin dashboard metrics and overview.
     */
    public function index(): JsonResponse
    {
        $metrics = Cache::remember('super_admin_dashboard_metrics', 300, function () {
            return CalculateDashboardMetricsAction::run();
        });

        return response()->json(['data' => $metrics]);
    }
}