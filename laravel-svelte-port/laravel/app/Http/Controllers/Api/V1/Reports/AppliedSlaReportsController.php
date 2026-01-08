<?php

namespace App\Http\Controllers\Api\V1\Reports;

use App\Http\Controllers\Controller;
use App\Repositories\Reports\AppliedSlaReportRepository;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppliedSlaReportsController extends Controller
{
    public function __construct(private AppliedSlaReportRepository $repo) {}

    /**
     * List applied SLAs for an account (paginated).
     */
    public function index(Account $account, Request $request): JsonResource
    {
        $perPage = (int) $request->get('per_page', 50);

        $results = $this->repo->getForAccount($account->id, $perPage);

        return JsonResource::collection($results);
    }

    /**
     * Return summary metrics grouped by SLA policy.
     */
    public function summary(Account $account)
    {
        $summary = $this->repo->summaryForAccount($account->id);

        return response()->json(['data' => $summary]);
    }
}
