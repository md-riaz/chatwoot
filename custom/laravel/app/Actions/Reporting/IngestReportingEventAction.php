<?php

namespace App\Actions\Reporting;

use App\Jobs\Reports\IngestReportingEventJob;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class IngestReportingEventAction
{
    use AsAction;

    public function handle(array $payload): void
    {
        $this->guardRequired($payload);

        IngestReportingEventJob::dispatch($payload)->onQueue('reports');
    }

    private function guardRequired(array $payload): void
    {
        $missing = [];

        foreach (['account_id', 'name'] as $field) {
            if (! array_key_exists($field, $payload) || $payload[$field] === null) {
                $missing[] = $field;
            }
        }

        if (! empty($missing)) {
            throw ValidationException::withMessages([
                'payload' => ['Missing required reporting fields: ' . implode(', ', $missing)],
            ]);
        }
    }
}
