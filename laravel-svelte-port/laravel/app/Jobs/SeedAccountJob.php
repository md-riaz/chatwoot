<?php

namespace App\Jobs;

use App\Models\Account;
use App\Services\Seeding\AccountSeederService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SeedAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Account $account
    ) {
        $this->onQueue('low');
    }

    public function handle(): void
    {
        try {
            Log::info('Starting account seeding', ['account_id' => $this->account->id]);
            
            $seeder = new AccountSeederService($this->account);
            $stats = $seeder->perform();
            
            Log::info('Account seeding completed', [
                'account_id' => $this->account->id,
                'stats' => $stats,
            ]);
        } catch (\Throwable $e) {
            Log::error('Account seeding failed', [
                'account_id' => $this->account->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}