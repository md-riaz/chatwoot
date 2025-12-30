<?php

namespace App\Repositories;

use App\Models\DataImport;

class DataImportRepository
{
    public function create(array $attrs): DataImport
    {
        return DataImport::create($attrs);
    }

    public function markProcessing(DataImport $import): DataImport
    {
        $import->update(['status' => DataImport::STATUS_PROCESSING]);
        return $import->refresh();
    }

    public function markCompleted(DataImport $import): DataImport
    {
        $import->update(['status' => DataImport::STATUS_COMPLETED]);
        return $import->refresh();
    }

    public function markFailed(DataImport $import, string $error): DataImport
    {
        $import->update(['status' => DataImport::STATUS_FAILED, 'error_message' => $error]);
        return $import->refresh();
    }
}
