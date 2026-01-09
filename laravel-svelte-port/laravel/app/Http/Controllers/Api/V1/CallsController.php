<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Voice\InitiateOutboundCallAction;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Inbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CallsController extends Controller
{
    /**
     * Initiate an outbound call to a contact.
     */
    public function create(Request $request, Account $account, Contact $contact): JsonResponse
    {
        $validated = $request->validate([
            'inbox_id' => 'required|integer|exists:inboxes,id',
        ]);

        // Verify contact belongs to account
        abort_unless($contact->account_id === $account->id, 404);

        // Verify inbox belongs to account and is a Voice channel
        $inbox = $account->inboxes()
            ->where('id', $validated['inbox_id'])
            ->where('channel_type', \App\Models\Channels\Voice::class)
            ->firstOrFail();

        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        // Verify user has access to this account
        $accountUser = $user->accountUsers()->where('account_id', $account->id)->first();
        if (!$accountUser) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $result = InitiateOutboundCallAction::run($account, $inbox, $user, $contact);

            return response()->json([
                'data' => [
                    'conversation_id' => $result['conversation']->id,
                    'call_sid' => $result['call_sid'],
                    'conference_sid' => $result['conference_sid'],
                    'status' => 'initiated',
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to initiate call',
                'message' => $e->getMessage()
            ], 422);
        }
    }
}