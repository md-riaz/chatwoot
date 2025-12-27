<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\CsatSurveyResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CsatSurveyController extends Controller
{
    /**
     * Show CSAT survey for a conversation.
     */
    public function show(string $uuid): JsonResponse
    {
        $conversation = Conversation::where('uuid', $uuid)->firstOrFail();

        // Check if CSAT is enabled for this inbox
        if (! $conversation->inbox->csat_survey_enabled) {
            return response()->json(['error' => 'CSAT survey is not enabled'], 404);
        }

        return response()->json([
            'data' => [
                'conversation_id' => $conversation->id,
                'inbox_id' => $conversation->inbox_id,
                'contact_id' => $conversation->contact_id,
            ],
        ]);
    }

    /**
     * Submit CSAT survey response.
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $conversation = Conversation::where('uuid', $uuid)->firstOrFail();

        // Check if CSAT is enabled for this inbox
        if (! $conversation->inbox->csat_survey_enabled) {
            return response()->json(['error' => 'CSAT survey is not enabled'], 404);
        }

        // Check if conversation is resolved
        if ($conversation->status !== Conversation::STATUS_RESOLVED) {
            return response()->json(['error' => 'Cannot submit CSAT for open conversation'], 422);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback_message' => 'nullable|string|max:1000',
        ]);

        // Check if CSAT response already exists
        $existingResponse = CsatSurveyResponse::where('conversation_id', $conversation->id)->first();

        if ($existingResponse) {
            $existingResponse->update([
                'rating' => $validated['rating'],
                'feedback_message' => $validated['feedback_message'] ?? null,
            ]);
            $csatResponse = $existingResponse;
        } else {
            $csatResponse = CsatSurveyResponse::create([
                'account_id' => $conversation->account_id,
                'conversation_id' => $conversation->id,
                'contact_id' => $conversation->contact_id,
                'assigned_agent_id' => $conversation->assignee_id,
                'rating' => $validated['rating'],
                'feedback_message' => $validated['feedback_message'] ?? null,
            ]);
        }

        return response()->json([
            'data' => $csatResponse,
        ]);
    }
}
