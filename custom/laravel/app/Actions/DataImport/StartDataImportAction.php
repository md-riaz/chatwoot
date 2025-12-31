<?php

namespace App\Actions\DataImport;

use App\Jobs\ImportContactsJob;
use App\Models\Account;
use App\Repositories\DataImportRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class StartDataImportAction
{
    use AsAction;

    public function __construct(private DataImportRepository $repository) {}

    public function handle(Account $account, int $userId, string $path, array $mapping = [], string $duplicateHandling = 'skip'): array
    {
        $trackingToken = (string) Str::uuid();

        $import = $this->repository->create([
            'account_id' => $account->id,
            'user_id' => $userId,
            'import_type' => 'contacts',
            'file_path' => $path,
            'status' => \App\Models\DataImport::STATUS_PENDING,
            'processed_rows' => 0,
            'meta' => [
                'mapping' => $mapping,
                'duplicate_handling' => $duplicateHandling,
                'tracking_token' => $trackingToken,
            ],
        ]);

        Cache::put("import_status:{$trackingToken}", [
            'status' => 'queued',
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => [],
            'data_import_id' => $import->id,
        ], now()->addHours(6));

        ImportContactsJob::dispatch(
            $account->id,
            $userId,
            $path,
            $trackingToken,
            $mapping,
            $duplicateHandling,
            $import->id
        )->onQueue('imports');

        return [
            'import_id' => $trackingToken,
            'data_import_id' => $import->id,
        ];
    }
}
