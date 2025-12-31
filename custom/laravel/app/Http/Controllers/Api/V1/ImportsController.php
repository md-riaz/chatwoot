<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\DataImport\GetImportStatusAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImportsController extends Controller
{
    /**
     * Public import status endpoint.
     * GET /imports/{import_id}/status
     */
    public function status(Request $request, string $import_id): JsonResponse
    {
        $status = GetImportStatusAction::run($import_id);

        if (! $status) {
            return response()->json(['error' => 'not_found'], 404);
        }

        return response()->json(['data' => $status]);
    }
}
