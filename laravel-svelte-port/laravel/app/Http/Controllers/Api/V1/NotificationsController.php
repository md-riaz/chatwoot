<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationsController extends Controller
{
    /**
     * Get notifications for the authenticated user (account scoped).
     */
    public function index(Request $request, Account $account): AnonymousResourceCollection
    {
        $user = auth()->user();
        
        // Rails parity: direct column scoping
        $query = $user->notifications()
            ->where('account_id', $account->id);

        $includes = $request->input('includes', []);
        
        // Filter read notifications
        // If 'read' is NOT in includes, we only show unread (where read_at is null).
        // If 'read' IS in includes, we show everything (don't filter).
        if (!in_array('read', $includes)) {
             $query->whereNull('read_at');
        }

        // Filter snoozed notifications
        // If 'snoozed' is NOT in includes, we only show unsnoozed (where snoozed_until is null).
        if (!in_array('snoozed', $includes)) {
             $query->whereNull('snoozed_until');
        }

        // Apply sort order
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy('last_activity_at', $sortOrder);
        
        $notifications = $query->paginate(15);
        
        // Calculate unread count for this account
        $unreadCount = $user->notifications()
            ->where('account_id', $account->id)
            ->whereNull('read_at')
            ->count();

        return NotificationResource::collection($notifications)->additional([
            'meta' => [
                'unread_count' => $unreadCount,
            ],
        ]);
    }

    /**
     * Get unread count.
     */
    public function unreadCount(Account $account): JsonResponse
    {
        $count = auth()->user()->notifications()
            ->where('account_id', $account->id)
            ->whereNull('read_at')
            ->count();
            
        return response()->json(['unreadCount' => $count]);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Account $account, $id): JsonResponse
    {
        $notification = auth()->user()->notifications()
            ->where('id', $id)
            ->where('account_id', $account->id)
            ->firstOrFail();
            
        $notification->markAsRead();
        
        return response()->json(new NotificationResource($notification));
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Account $account): JsonResponse
    {
        auth()->user()->notifications()
            ->where('account_id', $account->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
            
        return response()->json(['message' => 'All notifications marked as read']);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Account $account, $id): JsonResponse
    {
        $notification = auth()->user()->notifications()
            ->where('id', $id)
            ->where('account_id', $account->id)
            ->firstOrFail();
            
        $notification->delete();
        
        return response()->json(['message' => 'Notification deleted']);
    }

    /**
     * Delete all notifications.
     */
    public function destroyAll(Request $request, Account $account): JsonResponse
    {
        $type = $request->query('type', 'read'); // 'read' or 'all'

        $query = auth()->user()->notifications()
            ->where('data->account_id', $account->id);

        if ($type === 'read') {
            $query->whereNotNull('read_at');
        }
        
        $query->delete();

        return response()->json(['message' => 'Notifications deleted']);
    }
}
