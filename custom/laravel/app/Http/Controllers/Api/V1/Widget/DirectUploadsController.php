<?php

namespace App\Http\Controllers\Api\V1\Widget;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DirectUploadsController extends BaseController
{
    /**
     * Create a direct upload for file attachments.
     * POST /api/v1/widget/direct_uploads
     */
    public function store(Request $request): JsonResponse
    {
        $contactInbox = $this->resolveContactInbox($request);

        if (!$contactInbox) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'file' => 'required|file|max:40960', // 40MB max
        ]);

        $file = $request->file('file');
        $path = $file->store('attachments', 'public');

        return response()->json([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $this->getFileType($file->getMimeType()),
            'data_url' => Storage::disk('public')->url($path),
        ], 201);
    }

    /**
     * Get the file type based on mime type.
     */
    private function getFileType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        if (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }

        return 'file';
    }
}
