<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Attachment;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AttachmentsController extends Controller
{
    /**
     * Display a listing of attachments for a conversation.
     */
    public function index(Account $account, Conversation $conversation): JsonResource
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $attachments = Attachment::whereHas('message', function ($q) use ($conversation) {
            $q->where('conversation_id', $conversation->id);
        })->paginate();

        return JsonResource::collection($attachments);
    }

    /**
     * Upload an attachment.
     */
    public function store(Request $request, Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $validated = $request->validate([
            'file' => 'required|file|max:40960', // 40MB max
            'message_id' => 'nullable|exists:messages,id',
        ]);

        $file = $request->file('file');
        $path = $file->store('attachments/' . $account->id, 'public');

        $attachment = Attachment::create([
            'file_type' => $this->getFileType($file->getMimeType()),
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'content_type' => $file->getMimeType(),
            'account_id' => $account->id,
            'message_id' => $validated['message_id'] ?? null,
            'data_url' => Storage::url($path),
            'extension' => $file->getClientOriginalExtension(),
        ]);

        return response()->json(['data' => $attachment], 201);
    }

    /**
     * Display the specified attachment.
     */
    public function show(Account $account, Conversation $conversation, Attachment $attachment): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        return response()->json(['data' => $attachment]);
    }

    /**
     * Remove the specified attachment.
     */
    public function destroy(Account $account, Conversation $conversation, Attachment $attachment): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        // Delete file from storage
        if ($attachment->data_url) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $attachment->data_url));
        }

        $attachment->delete();

        return response()->json(null, 204);
    }

    /**
     * Get file type from mime type.
     */
    private function getFileType(string $mimeType): int
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 0; // image
        }
        if (str_starts_with($mimeType, 'audio/')) {
            return 1; // audio
        }
        if (str_starts_with($mimeType, 'video/')) {
            return 2; // video
        }

        return 3; // file
    }
}
