<?php

namespace App\Jobs\Sla;

use App\Models\Account;
use App\Models\AppliedSla;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAccountAppliedSlasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Account $account
    ) {
        $this->queue = 'medium';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->account->appliedSlas()
            ->whereIn('sla_status', [
                AppliedSla::STATUS_ACTIVE,
                AppliedSla::STATUS_ACTIVE_WITH_MISSES
            ])
            ->chunk(100, function ($appliedSlas) {
                foreach ($appliedSlas as $appliedSla) {
                    ProcessAppliedSlaJob::dispatch($appliedSla);
                }
            });
    }
}