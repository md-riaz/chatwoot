<?php

namespace App\Http\Controllers\Api\V1\Widget;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InboxMembersController extends BaseController
{
    /**
     * Get inbox members (available agents).
     * GET /api/v1/widget/inbox_members
     */
    public function index(Request $request): JsonResponse
    {
        $contactInbox = $this->resolveContactInbox($request);

        if (!$contactInbox) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $inbox = $contactInbox->inbox;

        // Get agents assigned to this inbox
        $members = $inbox->members()->with('user')->get();

        return response()->json([
            'data' => $members->map(function ($member) {
                return [
                    'id' => $member->user->id,
                    'name' => $member->user->name,
                    'avatar_url' => $member->user->getAvatarUrl(),
                    'availability_status' => $member->user->availability ?? 'offline',
                ];
            }),
        ]);
    }
}
