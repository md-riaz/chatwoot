<?php

namespace App\Jobs;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ExportContactsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $accountId, protected int $userId, protected array $columns = [], protected array $filters = []) {}

    public function handle(): void
    {
        $filename = 'exports/contacts_' . $this->accountId . '_' . time() . '.csv';
        $fullPath = Storage::path($filename);

        $handle = fopen($fullPath, 'w');
        if (! $handle) {
            return;
        }

        $columns = $this->columns ?: ['id', 'name', 'email', 'phone_number', 'identifier', 'created_at'];
        fputcsv($handle, $columns);

        $query = Contact::where('account_id', $this->accountId);

        // apply simple filters if provided
        if (! empty($this->filters['label'])) {
            // labels relationship not implemented here; skipping
        }

        $query->chunk(200, function ($contacts) use ($columns, $handle) {
            foreach ($contacts as $c) {
                $row = [];
                foreach ($columns as $col) {
                    $row[] = $c->{$col} ?? '';
                }
                fputcsv($handle, $row);
            }
        });

        fclose($handle);

        // Store file to storage disk (move into storage/app)
        $storagePath = 'exports/' . basename($fullPath);
        Storage::put($storagePath, file_get_contents($fullPath));

        // Cache the download path and notify the user
        Cache::put('export_result:' . $this->userId, $storagePath, now()->addHours(24));

        // Notify user (include account for download link generation)
        $user = \App\Models\User::find($this->userId);
        if ($user) {
            $user->notify(new \App\Notifications\ExportReadyNotification($storagePath, $this->accountId));
        }
    }
}
