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
     * Process bulk actions (main endpoint matching Chatwoot Rails API).
     * Accepts 'type' as Conversation or Contact.
     */
    public function store(Account $account, Request $request): JsonResponse
    {
        $type = $request->input('type', '');
        $normalizedType = ucfirst(strtolower($type));

        if ($normalizedType === 'Conversation') {
            return $this->processConversations($account, $request);
        } elseif ($normalizedType === 'Contact') {
            return $this->processContacts($account, $request);
        }

        return response()->json(['success' => false], 422);
    }

    /**
     * Process bulk conversation actions.
     */
    private function processConversations(Account $account, Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        $fields = $request->input('fields', []);
        $labels = $request->input('labels', []);
        $snoozedUntil = $request->input('snoozed_until');

        // Validate ids is an array and not empty
        if (! is_array($ids) || empty($ids)) {
            return response()->json(['error' => 'ids parameter must be a non-empty array'], 422);
        }

        $conversations = Conversation::where('account_id', $account->id)
            ->whereIn('display_id', $ids)
            ->get();

        $updated = 0;

        foreach ($conversations as $conversation) {
            $updateData = [];

            // Handle fields
            if (isset($fields['assignee_id'])) {
                $updateData['assignee_id'] = $fields['assignee_id'];
            }
            if (isset($fields['team_id'])) {
                $updateData['team_id'] = $fields['team_id'];
            }
            if (isset($fields['status'])) {
                $updateData['status'] = $fields['status'];
            }
            if (isset($fields['priority'])) {
                $updateData['priority'] = $fields['priority'];
            }

            // Handle snooze
            if ($snoozedUntil) {
                $updateData['status'] = 'snoozed';
                $updateData['snoozed_until'] = $snoozedUntil;
            }

            // Handle labels
            if (! empty($labels['add'])) {
                $currentLabels = $conversation->labels()->pluck('id')->toArray();
                $conversation->labels()->syncWithoutDetaching($labels['add']);
            }
            if (! empty($labels['remove'])) {
                $conversation->labels()->detach($labels['remove']);
            }

            if (! empty($updateData)) {
                $conversation->update($updateData);
            }

            $updated++;
        }

        return response()->json(null, 200);
    }

    /**
     * Process bulk contact actions.
     */
    private function processContacts(Account $account, Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        $actionName = $request->input('action_name');

        if (empty($ids)) {
            return response()->json(['error' => 'ids parameter is required'], 422);
        }

        if ($actionName === 'delete') {
            $account->contacts()->whereIn('id', $ids)->delete();
        }

        return response()->json(null, 200);
    }

    /**
     * Perform bulk actions on conversations (legacy endpoint).
     */
    public function conversations(Account $account, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:assign_team,assign_agent,update_status,add_labels,remove_labels,snooze,mute,resolve',
            'ids' => 'required|array',
            'fields' => 'nullable|array',
        ]);

        $conversations = Conversation::where('account_id', $account->id)
            ->whereIn('display_id', $validated['ids'])
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

                case 'snooze':
                    if (isset($validated['fields']['snoozed_until'])) {
                        $conversation->update([
                            'status' => 'snoozed',
                            'snoozed_until' => $validated['fields']['snoozed_until'],
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
        ]);

        $deleted = Conversation::where('account_id', $account->id)
            ->whereIn('display_id', $validated['ids'])
            ->delete();

        return response()->json([
            'message' => 'Bulk delete completed',
            'deleted' => $deleted,
        ]);
    }
}
