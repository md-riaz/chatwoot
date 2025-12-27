<?php

namespace App\Http\Controllers\Api\V1\Widget;

use App\Models\Label;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LabelsController extends BaseController
{
    /**
     * Add a label to the current conversation.
     * POST /api/v1/widget/labels
     */
    public function store(Request $request): JsonResponse
    {
        $contactInbox = $this->resolveContactInbox($request);

        if (!$contactInbox) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'label' => 'required|string|max:255',
        ]);

        $conversation = \App\Models\Conversation::where('contact_inbox_id', $contactInbox->id)
            ->latest()
            ->first();

        if (!$conversation) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        // Find or create the label
        $label = Label::firstOrCreate(
            [
                'account_id' => $contactInbox->inbox->account_id,
                'title' => $validated['label'],
            ],
            [
                'color' => '#' . substr(md5($validated['label']), 0, 6),
            ]
        );

        // Attach to conversation if not already attached
        if (!$conversation->labels()->where('labels.id', $label->id)->exists()) {
            $conversation->labels()->attach($label->id);
        }

        return response()->json(['success' => true], 201);
    }

    /**
     * Remove a label from the current conversation.
     * DELETE /api/v1/widget/labels/{label}
     */
    public function destroy(Request $request, Label $label): JsonResponse
    {
        $contactInbox = $this->resolveContactInbox($request);

        if (!$contactInbox) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $conversation = \App\Models\Conversation::where('contact_inbox_id', $contactInbox->id)
            ->latest()
            ->first();

        if (!$conversation) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        $conversation->labels()->detach($label->id);

        return response()->json(null, 204);
    }
}
