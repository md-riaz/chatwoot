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

class DraftMessagesController extends Controller
{
    /**
     * Get the draft message for a conversation.
     */
    public function show(Account $account, Conversation $conversation): DraftMessageResource
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $draftData = ManageDraftMessageAction::run()->getDraft($conversation, auth()->id());

        return new DraftMessageResource($draftData);
    }

    /**
     * Update (save) the draft message for a conversation.
     */
    public function update(Request $request, Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $draftMessageData = DraftMessageData::from($request->input('draft_message'));

        $draftData = ManageDraftMessageAction::run()->saveDraft(
            $conversation,
            auth()->id(),
            $draftMessageData->message,
            $draftMessageData->updated_at
        );

        return response()->json([
            'message' => 'Draft saved successfully',
            'updated_at' => $draftData['updated_at'],
        ]);
    }

    /**
     * Delete the draft message for a conversation.
     */
    public function destroy(Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        ManageDraftMessageAction::run()->deleteDraft($conversation, auth()->id());

        return response()->json(null, 204);
    }
}
