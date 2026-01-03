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
        $request->validate([
            'inbox_id' => 'required|integer|exists:inboxes,id',
        ]);

        // Ensure contact belongs to account
        abort_unless($contact->account_id === $account->id, 404);

        // Get voice inbox
        $inbox = $account->inboxes()
            ->where('id', $request->input('inbox_id'))
            ->where('channel_type', 'App\\Models\\Channels\\Voice')
            ->firstOrFail();

        // Ensure user has access to this inbox
        $user = $request->user();
        
        $result = InitiateOutboundCallAction::run($account, $inbox, $user, $contact);

        return response()->json([
            'conversation_id' => $result['conversation']->display_id,
            'inbox_id' => $inbox->id,
            'call_sid' => $result['call_sid'],
            'conference_sid' => $result['conference_sid'],
        ]);
    }
}