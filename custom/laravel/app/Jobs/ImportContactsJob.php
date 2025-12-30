<?php

namespace App\Jobs;

use App\Models\Account;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Notifications\ImportCompletedNotification;

class ImportContactsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param int $accountId
     * @param int $userId
     * @param string $path
     * @param string $importId
     * @param array $mapping
     * @param string $duplicateHandling
     */
    public function __construct(protected int $accountId, protected int $userId, protected string $path, protected string $importId = '', protected array $mapping = [], protected string $duplicateHandling = 'skip') {}

    public function handle(): void
    {
        $stream = Storage::readStream($this->path);
        if (! $stream) {
            Cache::put("import_status:{$this->importId}", ['status' => 'failed', 'error' => 'file_not_readable'], now()->addHours(6));
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
                Cache::put("import_status:{$this->importId}", ['status' => 'processing', 'processed' => $processed, 'created' => $created, 'updated' => $updated, 'errors' => $errors], now()->addHours(6));
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
                Cache::put("import_status:{$this->importId}", ['status' => 'processing', 'processed' => $processed, 'created' => $created, 'updated' => $updated, 'errors' => $errors], now()->addHours(6));
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
            Cache::put("import_status:{$this->importId}", ['status' => 'processing', 'processed' => $processed, 'created' => $created, 'updated' => $updated, 'errors' => $errors], now()->addHours(6));
        }

        if (is_resource($stream)) {
            fclose($stream);
        }

        $result = ['status' => 'completed', 'processed' => $processed, 'created' => $created, 'updated' => $updated, 'errors' => $errors];
        Cache::put("import_status:{$this->importId}", $result, now()->addHours(24));

        // Notify user about import completion
        $user = User::find($this->userId);
        if ($user) {
            $user->notify(new ImportCompletedNotification($result));
        }
    }
}
