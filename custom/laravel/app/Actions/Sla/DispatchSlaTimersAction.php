<?php

namespace App\Actions\Sla;

use App\Jobs\Sla\CheckSlaJob;
use App\Jobs\Sla\CreateSlaEventsJob;
use App\Models\Conversation;
use Lorisleiva\Actions\Concerns\AsAction;

class DispatchSlaTimersAction
{
    use AsAction;

    public function handle(Conversation $conversation): void
    {
        CheckSlaJob::withChain([
            (new CreateSlaEventsJob($conversation->id))->onQueue('sla'),
        ])->dispatch($conversation->id)->onQueue('sla');
    }
}
