<?php

namespace App\Http\Controllers\Api\V1\Widget;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventsController extends BaseController
{
    /**
     * Create an event for tracking purposes.
     * POST /api/v1/widget/events
     */
    public function store(Request $request): JsonResponse
    {
        $contactInbox = $this->resolveContactInbox($request);

        if (!$contactInbox) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'data' => 'nullable|array',
        ]);

        // Log the event or store in a tracking table
        // For now, we just acknowledge the event
        // EventTrackingJob::dispatch($contactInbox, $validated);

        return response()->json(['success' => true], 201);
    }
}
