<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Inbox;
use App\Services\Voice\Provider\Twilio\TokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConferenceController extends Controller
{
    /**
     * Generate Twilio access token for WebRTC voice calls.
     */
    public function token(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'inbox_id' => 'required|integer|exists:inboxes,id',
        ]);

        $inbox = $account->inboxes()
            ->where('id', $validated['inbox_id'])
            ->where('channel_type', \App\Models\Channels\Voice::class)
            ->firstOrFail();

        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $tokenService = new TokenService($inbox, $user, $account);
        $tokenData = $tokenService->generate();

        return response()->json([
            'data' => $tokenData
        ]);
    }
}