<?php

use App\Jobs\Assignment\AutoAssignConversationsJob;
use App\Jobs\Conversation\AutoResolveConversationJob;
use App\Models\Conversation;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Jobs
|--------------------------------------------------------------------------
|
| Here you may define all of your scheduled jobs that run on a recurring
| basis. These jobs will run automatically based on the schedule.
|
*/

// Auto-resolve stale conversations (every hour)
Schedule::call(function () {
    Conversation::where('status', Conversation::STATUS_OPEN)
        ->where('last_activity_at', '<', now()->subHours(48))
        ->each(fn ($conv) => AutoResolveConversationJob::dispatch($conv->id));
})->hourly()->name('auto-resolve-conversations');

// Auto-assign unassigned conversations (every 5 minutes)
Schedule::job(new AutoAssignConversationsJob)->everyFiveMinutes()->name('auto-assign-conversations');

// Cleanup old sessions (daily)
Schedule::command('auth:clear-resets')->daily()->name('clear-password-resets');

// Prune telescope entries (daily) - if installed
// Schedule::command('telescope:prune')->daily();

// Horizon snapshot for metrics (every 5 minutes)
Schedule::command('horizon:snapshot')->everyFiveMinutes()->name('horizon-snapshot');
