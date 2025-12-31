<?php

namespace App\Actions\DataImport;

use App\Repositories\DataImportRepository;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

class GetImportStatusAction
{
    use AsAction;

    public function __construct(private DataImportRepository $repository) {}

    public function handle(string $importId): ?array
    {
        $status = Cache::get("import_status:{$importId}");

        if ($status) {
            return $status;
        }

        $import = $this->repository->findByToken($importId);

        if (! $import) {
            return null;
        }

        return [
            'status' => $import->status,
            'processed' => $import->processed_rows,
            'errors' => $import->meta['errors'] ?? [],
            'created' => $import->meta['created'] ?? 0,
            'updated' => $import->meta['updated'] ?? 0,
            'data_import_id' => $import->id,
        ];
    }
}
