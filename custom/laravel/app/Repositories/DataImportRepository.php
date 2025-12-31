<?php

namespace App\Repositories;

use App\Models\DataImport;

class DataImportRepository
{
    public function find(int $id): ?DataImport
    {
        return DataImport::find($id);
    }

    public function findByToken(string $token): ?DataImport
    {
        return DataImport::where('meta->tracking_token', $token)->first();
    }

    public function create(array $attrs): DataImport
    {
        return DataImport::create($attrs);
    }

    public function markProcessing(DataImport $import): DataImport
    {
        $import->update(['status' => DataImport::STATUS_PROCESSING]);
        return $import->refresh();
    }

    public function markCompleted(DataImport $import, array $meta = []): DataImport
    {
        $payload = ['status' => DataImport::STATUS_COMPLETED];

        if (array_key_exists('processed_rows', $meta)) {
            $payload['processed_rows'] = $meta['processed_rows'];
            unset($meta['processed_rows']);
        }

        if (array_key_exists('total_rows', $meta)) {
            $payload['total_rows'] = $meta['total_rows'];
            unset($meta['total_rows']);
        }

        if (! empty($meta)) {
            $payload['meta'] = array_merge($import->meta ?? [], $meta);
        }

        $import->update($payload);
        return $import->refresh();
    }

    public function markFailed(DataImport $import, string $error, array $meta = []): DataImport
    {
        $payload = [
            'status' => DataImport::STATUS_FAILED,
            'error_message' => $error,
        ];

        if (! empty($meta)) {
            $payload['meta'] = array_merge($import->meta ?? [], $meta);
        }

        $import->update($payload);
        return $import->refresh();
    }

    public function updateProgress(DataImport $import, int $processedRows, array $meta = []): DataImport
    {
        $payload = ['processed_rows' => $processedRows];

        if (! empty($meta)) {
            $payload['meta'] = array_merge($import->meta ?? [], $meta);
        }

        $import->update($payload);

        return $import->refresh();
    }
}
