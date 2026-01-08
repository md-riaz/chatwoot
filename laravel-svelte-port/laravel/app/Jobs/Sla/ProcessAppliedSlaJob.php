<?php

namespace App\Jobs\Sla;

use App\Actions\Sla\EvaluateAppliedSlaAction;
use App\Models\AppliedSla;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAppliedSlaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private AppliedSla $appliedSla
    ) {
        $this->queue = 'medium';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        EvaluateAppliedSlaAction::run($this->appliedSla);
    }
}