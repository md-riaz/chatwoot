<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Services\Contact\ContactFilterService;
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

    public function __construct(
        protected int $accountId, 
        protected int $userId, 
        protected array $columns = [], 
        protected array $filters = []
    ) {}

    public function handle(ContactFilterService $filterService): void
    {
        $filename = 'exports/contacts_' . $this->accountId . '_' . time() . '.csv';
        $fullPath = Storage::path($filename);

        // Ensure directory exists
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $handle = fopen($fullPath, 'w');
        if (!$handle) {
            throw new \Exception('Could not create export file');
        }

        // Determine columns to export
        $columns = $this->getExportColumns();
        fputcsv($handle, $columns);

        // Get filtered contacts query
        $query = $this->getFilteredContactsQuery($filterService);

        // Export contacts in chunks
        $query->chunk(200, function ($contacts) use ($columns, $handle) {
            foreach ($contacts as $contact) {
                $row = [];
                foreach ($columns as $col) {
                    $row[] = $this->getContactValue($contact, $col);
                }
                fputcsv($handle, $row);
            }
        });

        fclose($handle);

        // Store file to storage disk
        $storagePath = 'exports/' . basename($fullPath);
        Storage::put($storagePath, file_get_contents($fullPath));

        // Clean up temporary file
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        // Cache the download path and notify the user
        Cache::put('export_result:' . $this->userId, $storagePath, now()->addHours(24));

        // Notify user
        $user = \App\Models\User::find($this->userId);
        if ($user) {
            $user->notify(new \App\Notifications\ExportReadyNotification($storagePath, $this->accountId));
        }
    }

    private function getExportColumns(): array
    {
        $defaultColumns = ['id', 'name', 'email', 'phone_number', 'identifier', 'created_at'];
        
        if (!empty($this->columns)) {
            // Validate requested columns against available columns
            $availableColumns = array_merge($defaultColumns, [
                'blocked', 'last_activity_at', 'updated_at'
            ]);
            return array_intersect($this->columns, $availableColumns);
        }

        return $defaultColumns;
    }

    private function getFilteredContactsQuery(ContactFilterService $filterService)
    {
        $baseQuery = Contact::where('account_id', $this->accountId);

        // Apply filters if provided
        if (!empty($this->filters['payload']) && is_array($this->filters['payload'])) {
            $baseQuery = $filterService->applyFilters($baseQuery, $this->filters['payload']);
        }

        // Apply label filter if provided
        if (!empty($this->filters['label'])) {
            $baseQuery = $baseQuery->whereHas('labels', function ($query) {
                $query->where('title', $this->filters['label']);
            });
        }

        // Only export resolved contacts (non-blocked, with valid contact info)
        $baseQuery = $baseQuery->where(function ($query) {
            $query->whereNotNull('email')
                  ->orWhereNotNull('phone_number')
                  ->orWhereNotNull('identifier');
        });

        return $baseQuery->orderBy('created_at', 'desc');
    }

    private function getContactValue(Contact $contact, string $column): string
    {
        switch ($column) {
            case 'id':
                return (string) $contact->id;
            case 'name':
                return $contact->name ?? '';
            case 'email':
                return $contact->email ?? '';
            case 'phone_number':
                return $contact->phone_number ?? '';
            case 'identifier':
                return $contact->identifier ?? '';
            case 'blocked':
                return $contact->blocked ? 'true' : 'false';
            case 'created_at':
                return $contact->created_at?->toISOString() ?? '';
            case 'updated_at':
                return $contact->updated_at?->toISOString() ?? '';
            case 'last_activity_at':
                return $contact->last_activity_at?->toISOString() ?? '';
            default:
                // Check additional attributes
                if (isset($contact->additional_attributes[$column])) {
                    return (string) $contact->additional_attributes[$column];
                }
                // Check custom attributes
                if (isset($contact->custom_attributes[$column])) {
                    return (string) $contact->custom_attributes[$column];
                }
                return '';
        }
    }
}
