<?php

namespace App\Http\Controllers\Api\V1\Conversations;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DraftMessagesController extends Controller
{
    /**
     * Get the draft message for a conversation.
     */
    public function show(Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $cacheKey = $this->getDraftCacheKey($conversation);
        $draftMessage = Cache::get($cacheKey);

        if (! $draftMessage) {
            return response()->json(['has_draft' => false]);
        }

        return response()->json([
            'has_draft' => true,
            'message' => $draftMessage,
        ]);
    }

    /**
     * Update (save) the draft message for a conversation.
     */
    public function update(Request $request, Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $message = $request->input('draft_message.message', '');

        $cacheKey = $this->getDraftCacheKey($conversation);
        
        // Store draft for 24 hours
        Cache::put($cacheKey, $message, now()->addDay());

        return response()->json(null, 200);
    }

    /**
     * Delete the draft message for a conversation.
     */
    public function destroy(Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $cacheKey = $this->getDraftCacheKey($conversation);
        Cache::forget($cacheKey);

        return response()->json(null, 204);
    }

    /**
     * Get the cache key for the draft message.
     */
    private function getDraftCacheKey(Conversation $conversation): string
    {
        return "conversation_draft_message:{$conversation->id}";
    }
}
