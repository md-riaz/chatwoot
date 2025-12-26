<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Notification;

class NotificationsController extends Controller
{
    /**
     * Get notifications for the authenticated user.
     */
    public function index(Request $request): JsonResource
    {
        $user = auth()->user();
        
        $notifications = $user->notifications()
            ->when($request->has('read'), function ($q) use ($request) {
                if ($request->read === 'true') {
                    $q->whereNotNull('read_at');
                } else {
                    $q->whereNull('read_at');
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate();

        return JsonResource::collection($notifications);
    }

    /**
     * Get unread notification count.
     */
    public function unreadCount(): JsonResponse
    {
        $count = auth()->user()->unreadNotifications()->count();

        return response()->json(['data' => ['count' => $count]]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(string $notificationId): JsonResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        return response()->json(['data' => $notification]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read']);
    }

    /**
     * Delete a notification.
     */
    public function destroy(string $notificationId): JsonResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($notificationId);
        $notification->delete();

        return response()->json(null, 204);
    }

    /**
     * Delete all read notifications.
     */
    public function destroyAll(): JsonResponse
    {
        auth()->user()->notifications()->whereNotNull('read_at')->delete();

        return response()->json(['message' => 'All read notifications deleted']);
    }
}
