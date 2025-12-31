<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\DataImportRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ImportsController extends Controller
{
    public function __construct(private DataImportRepository $dataImportRepository) {}

    /**
     * Public import status endpoint.
     * GET /imports/{import_id}/status
     */
    public function status(Request $request, string $import_id): JsonResponse
    {
        $status = Cache::get("import_status:{$import_id}");

        if (! $status) {
            $import = $this->dataImportRepository->findByToken($import_id);

            if (! $import) {
                return response()->json(['error' => 'not_found'], 404);
            }

            $status = [
                'status' => $import->status,
                'processed' => $import->processed_rows,
                'errors' => $import->meta['errors'] ?? [],
                'created' => $import->meta['created'] ?? 0,
                'updated' => $import->meta['updated'] ?? 0,
            ];
        }

        return response()->json(['data' => $status]);
    }
}
