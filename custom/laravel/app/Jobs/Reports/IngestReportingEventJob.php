<?php

namespace App\Jobs\Reports;

use App\Models\ReportingEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class IngestReportingEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 120;

    public string $queue = 'reports';

    public function __construct(public array $payload) {}

    public function handle(): void
    {
        $event = ReportingEvent::create([
            'account_id' => $this->payload['account_id'],
            'conversation_id' => $this->payload['conversation_id'] ?? null,
            'inbox_id' => $this->payload['inbox_id'] ?? null,
            'user_id' => $this->payload['user_id'] ?? null,
            'name' => $this->payload['name'],
            'value' => $this->payload['value'] ?? null,
            'value_in_business_hours' => $this->payload['value_in_business_hours'] ?? null,
            'event_start_time' => $this->payload['event_start_time'] ?? now(),
            'event_end_time' => $this->payload['event_end_time'] ?? now(),
        ]);

        Log::info('Reporting event ingested', [
            'event_id' => $event->id,
            'name' => $event->name,
        ]);
    }
}
