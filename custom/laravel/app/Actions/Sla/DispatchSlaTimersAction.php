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
        CheckSlaJob::dispatch($conversation->id)->onQueue('sla');
        CreateSlaEventsJob::dispatch($conversation->id)->onQueue('sla');
    }
}
