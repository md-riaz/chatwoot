<?php

namespace App\Console\Commands;

use App\Actions\Conversations\ManageDraftMessageAction;
use Illuminate\Console\Command;

class CleanupDraftMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'drafts:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired draft messages';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Cleaning up expired draft messages...');
        
        $action = new ManageDraftMessageAction();
        $action->cleanupExpiredDrafts();
        
        $this->info('Draft cleanup completed.');
        
        return Command::SUCCESS;
    }
}