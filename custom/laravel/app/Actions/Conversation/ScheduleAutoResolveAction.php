<?php

namespace App\Actions\Conversation;

use App\Jobs\Conversation\AutoResolveConversationJob;
use App\Models\Conversation;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class ScheduleAutoResolveAction
{
    use AsAction;

    public function handle(Conversation $conversation): void
    {
        if ($conversation->status !== Conversation::STATUS_OPEN) {
            return;
        }

        $autoResolveMinutes = $conversation->account?->autoResolveAfterMinutes();
        if (! $autoResolveMinutes) {
            return;
        }

        $lastActivity = $conversation->last_activity_at ?: now();
        $deadline = Carbon::parse($lastActivity)->addMinutes($autoResolveMinutes);
        $delay = $deadline->isFuture() ? $deadline : now();

        AutoResolveConversationJob::dispatch($conversation->id)
            ->delay($delay)
            ->onQueue('conversations');
    }
}
