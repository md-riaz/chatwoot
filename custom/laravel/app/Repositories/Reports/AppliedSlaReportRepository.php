<?php

namespace App\Repositories\Reports;

use App\Models\AppliedSla;

class AppliedSlaReportRepository
{
    public function getForAccount(int $accountId, int $perPage = 50)
    {
        return AppliedSla::with(['slaPolicy', 'conversation'])
            ->where('account_id', $accountId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function summaryForAccount(int $accountId)
    {
        return AppliedSla::query()
            ->where('account_id', $accountId)
            ->selectRaw('sla_policy_id, COUNT(*) as total, SUM(CASE WHEN sla_resolution_at IS NOT NULL THEN 1 ELSE 0 END) as resolved')
            ->groupBy('sla_policy_id')
            ->get();
    }
}
