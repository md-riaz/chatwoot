<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BulkActionsController extends Controller
{
    /**
     * Perform bulk actions on conversations.
     */
    public function conversations(Account $account, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:assign_team,assign_agent,update_status,add_labels,remove_labels,snooze,mute,resolve',
            'ids' => 'required|array',
            'ids.*' => 'exists:conversations,id',
            'fields' => 'nullable|array',
        ]);

        $conversations = Conversation::where('account_id', $account->id)
            ->whereIn('id', $validated['ids'])
            ->get();

        $updated = 0;

        foreach ($conversations as $conversation) {
            switch ($validated['type']) {
                case 'assign_team':
                    if (isset($validated['fields']['team_id'])) {
                        $conversation->update(['team_id' => $validated['fields']['team_id']]);
                        $updated++;
                    }
                    break;

                case 'assign_agent':
                    if (isset($validated['fields']['assignee_id'])) {
                        $conversation->update(['assignee_id' => $validated['fields']['assignee_id']]);
                        $updated++;
                    }
                    break;

                case 'update_status':
                    if (isset($validated['fields']['status'])) {
                        $conversation->update(['status' => $validated['fields']['status']]);
                        $updated++;
                    }
                    break;

                case 'add_labels':
                    if (isset($validated['fields']['labels'])) {
                        $existingLabels = $conversation->labels ?? [];
                        $conversation->update([
                            'labels' => array_unique(array_merge($existingLabels, $validated['fields']['labels']))
                        ]);
                        $updated++;
                    }
                    break;

                case 'remove_labels':
                    if (isset($validated['fields']['labels'])) {
                        $existingLabels = $conversation->labels ?? [];
                        $conversation->update([
                            'labels' => array_diff($existingLabels, $validated['fields']['labels'])
                        ]);
                        $updated++;
                    }
                    break;

                case 'snooze':
                    if (isset($validated['fields']['snoozed_until'])) {
                        $conversation->update([
                            'status' => 'snoozed',
                            'snoozed_until' => $validated['fields']['snoozed_until']
                        ]);
                        $updated++;
                    }
                    break;

                case 'mute':
                    $conversation->update(['muted' => true]);
                    $updated++;
                    break;

                case 'resolve':
                    $conversation->update(['status' => 'resolved']);
                    $updated++;
                    break;
            }
        }

        return response()->json([
            'message' => 'Bulk action completed',
            'updated' => $updated,
        ]);
    }

    /**
     * Bulk delete conversations (soft delete).
     */
    public function deleteConversations(Account $account, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:conversations,id',
        ]);

        $deleted = Conversation::where('account_id', $account->id)
            ->whereIn('id', $validated['ids'])
            ->delete();

        return response()->json([
            'message' => 'Bulk delete completed',
            'deleted' => $deleted,
        ]);
    }
}
