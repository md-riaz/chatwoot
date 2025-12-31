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

            while (($row = fgetcsv($stream)) !== false) {
                if (! $header) {
                    $header = $row;
                    continue;
                }

                $data = array_combine($header, $row);
                if (! $data) {
                    $errors[] = ['row' => $processed + 1, 'error' => 'invalid_row'];
                    $processed++;
                    $this->persistProgress($imports, $importModel, $processed, $created, $updated, $errors);
                    continue;
                }

                // Build contact payload according to mapping
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

                // Basic validation
                $email = $contactPayload['email'] ?? null;
                if ($email && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = ['row' => $processed + 1, 'error' => 'invalid_email', 'value' => $email];
                    $processed++;
                    $this->persistProgress($imports, $importModel, $processed, $created, $updated, $errors);
                    continue;
                }

                // Duplicate detection by email, phone_number or identifier
                $existing = null;
                if (! empty($contactPayload['email'])) {
                    $existing = Contact::where('account_id', $this->accountId)->where('email', $contactPayload['email'])->first();
                }
                if (! $existing && ! empty($contactPayload['phone_number'])) {
                    $existing = Contact::where('account_id', $this->accountId)->where('phone_number', $contactPayload['phone_number'])->first();
                }
                if (! $existing && ! empty($contactPayload['identifier'])) {
                    $existing = Contact::where('account_id', $this->accountId)->where('identifier', $contactPayload['identifier'])->first();
                }

                try {
                    if ($existing) {
                        if ($this->duplicateHandling === 'skip') {
                            // do nothing
                        } elseif ($this->duplicateHandling === 'update') {
                            $existing->update($contactPayload);
                            $updated++;
                        } else { // create_duplicate
                            Contact::create($contactPayload);
                            $created++;
                        }
                    } else {
                        Contact::create($contactPayload);
                        $created++;
                    }
                } catch (\Exception $e) {
                    $errors[] = ['row' => $processed + 1, 'error' => $e->getMessage()];
                    report($e);
                }

                $processed++;
                $this->persistProgress($imports, $importModel, $processed, $created, $updated, $errors);
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
                ]);
                $importModel->update(['processed_rows' => $processed, 'total_rows' => $processed]);
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
}
