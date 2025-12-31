<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\User;
use App\Repositories\DataImportRepository;
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

    public function handle(DataImportRepository $imports): void
    {
        $importModel = $this->dataImportId ? $imports->find($this->dataImportId) : null;
        if ($importModel) {
            $imports->markProcessing($importModel);
        }

        try {
            $stream = Storage::readStream($this->path);
            if (! $stream) {
                Cache::put("import_status:{$this->importId}", ['status' => 'failed', 'error' => 'file_not_readable'], now()->addHours(6));
                if ($importModel) {
                    $imports->markFailed($importModel, 'file_not_readable');
                }

                return;
            }

            $header = null;
            $processed = 0;
            $created = 0;
            $updated = 0;
            $errors = [];
            $batch = [];
            $rowNumber = 0;

            while (($row = fgetcsv($stream)) !== false) {
                if (! $header) {
                    $header = $row;
                    continue;
                }

                $rowNumber++;
                $batch[] = ['row' => $row, 'row_number' => $rowNumber];

                if (count($batch) >= self::CSV_CHUNK_SIZE) {
                    $this->processBatch($batch, $header, $imports, $importModel, $processed, $created, $updated, $errors);
                    $batch = [];
                }
            }

            if (! empty($batch)) {
                $this->processBatch($batch, $header ?? [], $imports, $importModel, $processed, $created, $updated, $errors);
            }

            if (is_resource($stream)) {
                fclose($stream);
            }

            $result = ['status' => 'completed', 'processed' => $processed, 'created' => $created, 'updated' => $updated, 'errors' => $errors];
            Cache::put("import_status:{$this->importId}", $result, now()->addHours(24));

            if ($importModel) {
                $imports->markCompleted($importModel, [
                    'created' => $created,
                    'updated' => $updated,
                    'errors' => $errors,
                    'tracking_token' => $this->importId,
                    'processed_rows' => $processed,
                    'total_rows' => $processed,
                ]);
            }

            // Notify user about import completion
            $user = User::find($this->userId);
            if ($user) {
                $user->notify(new ImportCompletedNotification($result));
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

    private function processBatch(
        array $batch,
        array $header,
        DataImportRepository $imports,
        ?\App\Models\DataImport $importModel,
        int &$processed,
        int &$created,
        int &$updated,
        array &$errors
    ): void {
        $preparedRows = [];

        foreach ($batch as $rowData) {
            $data = $header ? array_combine($header, $rowData['row']) : false;
            if (! $data) {
                $errors[] = ['row' => $rowData['row_number'], 'error' => 'invalid_row'];
                $processed++;
                $this->maybePersistProgress($imports, $importModel, $processed, $created, $updated, $errors);
                continue;
            }

            $contactPayload = ['account_id' => $this->accountId];
            $custom = [];
            foreach ($data as $col => $val) {
                $mapped = $this->mapping[$col] ?? null;
                if ($mapped) {
                    $contactPayload[$mapped] = $val;
                } else {
                    $custom[$col] = $val;
                }
            }
            if (! empty($custom)) {
                $contactPayload['custom_attributes'] = $custom;
            }

            $email = $contactPayload['email'] ?? null;
            if ($email && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = ['row' => $rowData['row_number'], 'error' => 'invalid_email', 'value' => $email];
                $processed++;
                $this->maybePersistProgress($imports, $importModel, $processed, $created, $updated, $errors);
                continue;
            }

            $preparedRows[] = [
                'row_number' => $rowData['row_number'],
                'payload' => $contactPayload,
            ];
        }

        if (empty($preparedRows)) {
            return;
        }

        $emails = array_values(array_filter(array_unique(array_column(array_column($preparedRows, 'payload'), 'email'))));
        $phones = array_values(array_filter(array_unique(array_column(array_column($preparedRows, 'payload'), 'phone_number'))));
        $identifiers = array_values(array_filter(array_unique(array_column(array_column($preparedRows, 'payload'), 'identifier'))));

        $existingContacts = Contact::where('account_id', $this->accountId)
            ->where(function ($query) use ($emails, $phones, $identifiers) {
                if (! empty($emails)) {
                    $query->orWhereIn('email', $emails);
                }
                if (! empty($phones)) {
                    $query->orWhereIn('phone_number', $phones);
                }
                if (! empty($identifiers)) {
                    $query->orWhereIn('identifier', $identifiers);
                }
            })
            ->get();

        $existingByEmail = [];
        $existingByPhone = [];
        $existingByIdentifier = [];

        foreach ($existingContacts as $contact) {
            if ($contact->email) {
                $existingByEmail[$contact->email] = $contact;
            }
            if ($contact->phone_number) {
                $existingByPhone[$contact->phone_number] = $contact;
            }
            if ($contact->identifier) {
                $existingByIdentifier[$contact->identifier] = $contact;
            }
        }

        foreach ($preparedRows as $preparedRow) {
            $contactPayload = $preparedRow['payload'];
            $existing = null;

            if (! empty($contactPayload['email']) && isset($existingByEmail[$contactPayload['email']])) {
                $existing = $existingByEmail[$contactPayload['email']];
            } elseif (! empty($contactPayload['phone_number']) && isset($existingByPhone[$contactPayload['phone_number']])) {
                $existing = $existingByPhone[$contactPayload['phone_number']];
            } elseif (! empty($contactPayload['identifier']) && isset($existingByIdentifier[$contactPayload['identifier']])) {
                $existing = $existingByIdentifier[$contactPayload['identifier']];
            }

            try {
                if ($existing) {
                    if ($this->duplicateHandling === 'skip') {
                        // no-op
                    } elseif ($this->duplicateHandling === 'update') {
                        $existing->update($contactPayload);
                        $updated++;
                    } else {
                        Contact::create($contactPayload);
                        $created++;
                    }
                } else {
                    Contact::create($contactPayload);
                    $created++;
                }
            } catch (Throwable $e) {
                $errors[] = ['row' => $preparedRow['row_number'], 'error' => $e->getMessage()];
                report($e);
            }

            $processed++;
            $this->maybePersistProgress($imports, $importModel, $processed, $created, $updated, $errors);
        }
    }

    private function maybePersistProgress(
        DataImportRepository $imports,
        ?\App\Models\DataImport $importModel,
        int $processed,
        int $created,
        int $updated,
        array $errors
    ): void {
        if ($processed % self::PROGRESS_BATCH_SIZE === 0) {
            $this->persistProgress($imports, $importModel, $processed, $created, $updated, $errors);
        }
    }
}
