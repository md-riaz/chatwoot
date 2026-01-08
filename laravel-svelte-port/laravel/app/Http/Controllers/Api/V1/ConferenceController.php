<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Voice\EndConferenceAction;
use App\Actions\Voice\JoinConferenceAction;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Inbox;
use App\Services\Voice\Provider\Twilio\TokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConferenceController extends Controller
{
    /**
     * Get WebRTC token for voice calls.
     */
    public function token(Request $request, Account $account): JsonResponse
    {
        $inboxId = $request->input('inbox_id');
        
        if (!$inboxId) {
            return response()->json(['error' => 'inbox_id required'], 422);
        }

        $inbox = $account->inboxes()
            ->where('id', $inboxId)
            ->where('channel_type', 'App\\Models\\Channels\\Voice')
            ->firstOrFail();

        $user = $request->user();
        
        $tokenService = new TokenService($inbox, $user, $account);
        $tokenData = $tokenService->generate();

        return response()->json($tokenData);
    }

    /**
     * Join a conference call.
     */
    public function create(Request $request, Account $account): JsonResponse
    {
        $request->validate([
            'conversation_id' => 'required|string',
            'call_sid' => 'nullable|string',
        ]);

        $result = JoinConferenceAction::run(
            $account,
            $request->user(),
            $request->input('conversation_id'),
            $request->input('call_sid')
        );

        return response()->json($result);
    }

    /**
     * End a conference call.
     */
    public function destroy(Request $request, Account $account): JsonResponse
    {
        $request->validate([
            'conversation_id' => 'required|string',
        ]);

        $result = EndConferenceAction::run(
            $account,
            $request->input('conversation_id')
        );

        return response()->json($result);
    }
}