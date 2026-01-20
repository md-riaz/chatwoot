<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\User;
use App\Repositories\DataImportRepository;
use App\Services\Contact\ContactImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Notifications\ImportCompletedNotification;
use Throwable;

class ImportContactsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const CSV_CHUNK_SIZE = 200;

    private const PROGRESS_BATCH_SIZE = 100;

    public int $tries = 3;

    public int $backoff = 120;

    public int $timeout = 900;

    public string $queue = 'imports';

    /**
     * @param int $accountId
     * @param int $userId
     * @param string $path
     * @param string $importId
     * @param array $mapping
     * @param string $duplicateHandling
     */
    public function __construct(
        protected int $accountId,
        protected int $userId,
        protected string $path,
        protected string $importId = '',
        protected array $mapping = [],
        protected string $duplicateHandling = 'skip',
        protected ?int $dataImportId = null
    ) {}

    public function handle(DataImportRepository $imports, ContactImportService $importService): void
    {
        $importModel = $this->dataImportId ? $imports->find($this->dataImportId) : null;
        if ($importModel) {
            $imports->markProcessing($importModel);
        }

        try {
            $result = $importService->processImport(
                $this->accountId,
                $this->path,
                $this->mapping,
                $this->duplicateHandling,
                function ($processed, $created, $updated, $errors) use ($imports, $importModel) {
                    $this->persistProgress($imports, $importModel, $processed, $created, $updated, $errors);
                }
            );

            Cache::put("import_status:{$this->importId}", $result, now()->addHours(24));

            if ($importModel) {
                $imports->markCompleted($importModel, [
                    'created' => $result['created'],
                    'updated' => $result['updated'],
                    'errors' => $result['errors'],
                    'tracking_token' => $this->importId,
                    'processed_rows' => $result['processed'],
                    'total_rows' => $result['processed'],
                ]);

                // Save failed records CSV if there are errors
                if (!empty($result['errors']) && !empty($result['failed_records'])) {
                    $this->saveFailedRecordsCsv($importModel, $result['failed_records'], $imports);
                }
            }

            // Notify user about import completion
            $user = User::find($this->userId);
            if ($user) {
                $user->notify(new ImportCompletedNotification($result, $this->accountId));
            }
        } catch (Throwable $exception) {
            Cache::put("import_status:{$this->importId}", ['status' => 'failed', 'error' => $exception->getMessage()], now()->addHours(6));
            if ($importModel) {
                $imports->markFailed($importModel, $exception->getMessage());
            }

            throw $exception;
        }
    }

    private function persistProgress(DataImportRepository $imports, ?\App\Models\DataImport $importModel, int $processed, int $created, int $updated, array $errors): void
    {
        Cache::put("import_status:{$this->importId}", [
            'status' => 'processing',
            'processed' => $processed,
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
            'data_import_id' => $importModel?->id,
        ], now()->addHours(6));

        if ($importModel) {
            $imports->updateProgress($importModel, $processed, [
                'created' => $created,
                'updated' => $updated,
                'errors' => $errors,
                'tracking_token' => $this->importId,
            ]);
        }
    }

    private function saveFailedRecordsCsv(\App\Models\DataImport $importModel, array $failedRecords, DataImportRepository $imports): void
    {
        if (empty($failedRecords)) {
            return;
        }

        $csvContent = '';
        $headers = array_keys($failedRecords[0]);
        $headers[] = 'errors';
        
        $csvContent .= implode(',', array_map(function($header) {
            return '"' . str_replace('"', '""', $header) . '"';
        }, $headers)) . "\n";

        foreach ($failedRecords as $record) {
            $row = [];
            foreach ($headers as $header) {
                if ($header === 'errors') {
                    $row[] = '"' . str_replace('"', '""', $record['error_message'] ?? '') . '"';
                } else {
                    $row[] = '"' . str_replace('"', '""', $record[$header] ?? '') . '"';
                }
            }
            $csvContent .= implode(',', $row) . "\n";
        }

        $filename = 'failed_imports/contacts_' . $importModel->id . '_' . date('Y-m-d_H-i-s') . '.csv';
        Storage::put($filename, $csvContent);

        // Update import model with failed records file path
        $meta = $importModel->meta ?? [];
        $meta['failed_records_file'] = $filename;
        $importModel->update(['meta' => $meta]);
    }

    private function persistProgress(DataImportRepository $imports, ?\App\Models\DataImport $importModel, int $processed, int $created, int $updated, array $errors): void
    {
        Cache::put("import_status:{$this->importId}", [
            'status' => 'processing',
            'processed' => $processed,
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
            'data_import_id' => $importModel?->id,
        ], now()->addHours(6));

        if ($importModel) {
            $imports->updateProgress($importModel, $processed, [
                'created' => $created,
                'updated' => $updated,
                'errors' => $errors,
                'tracking_token' => $this->importId,
            ]);
        }
    }
}
