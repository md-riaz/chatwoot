<?php

namespace App\Http\Controllers\Api\V1\Conversations;

use App\Actions\Conversations\ManageDraftMessageAction;
use App\Data\Conversations\DraftMessageData;
use App\Http\Controllers\Controller;
use App\Http\Resources\DraftMessageResource;
use App\Models\Account;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DraftMessagesController extends Controller
{
    /**
     * Get the draft message for a conversation.
     */
    public function show(Account $account, Conversation $conversation): DraftMessageResource
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $action = new ManageDraftMessageAction();
        $draftData = $action->getDraft($conversation, auth()->id());

        return new DraftMessageResource($draftData);
    }

    /**
     * Update (save) the draft message for a conversation.
     */
    public function update(Request $request, Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        try {
            $draftMessageData = DraftMessageData::from($request->input('draft_message'));

            $action = new ManageDraftMessageAction();
            $draftData = $action->saveDraft(
                $conversation,
                auth()->id(),
                $draftMessageData->message,
                $draftMessageData->updated_at
            );

            Log::info('Draft message saved', [
                'conversation_id' => $conversation->id,
                'user_id' => auth()->id(),
                'message_length' => strlen($draftMessageData->message)
            ]);

            return response()->json([
                'message' => 'Draft saved successfully',
                'updated_at' => $draftData['updated_at'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Draft message validation failed', [
                'conversation_id' => $conversation->id,
                'user_id' => auth()->id(),
                'errors' => $e->errors()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to save draft message', [
                'conversation_id' => $conversation->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete the draft message for a conversation.
     */
    public function destroy(Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $action = new ManageDraftMessageAction();
        $action->deleteDraft($conversation, auth()->id());

        Log::info('Draft message deleted', [
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id()
        ]);

        return response()->json(null, 204);
    }
}
