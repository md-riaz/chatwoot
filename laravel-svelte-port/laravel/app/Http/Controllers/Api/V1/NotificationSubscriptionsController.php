<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationSubscriptionsController extends Controller
{
    /**
     * Create a notification subscription for push notifications.
     * POST /api/v1/notification_subscriptions
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subscription_type' => 'required|string|in:browser_push,fcm',
            'subscription_attributes' => 'required|array',
            'subscription_attributes.push_token' => 'required_if:subscription_type,fcm|string',
            'subscription_attributes.endpoint' => 'required_if:subscription_type,browser_push|string',
            'subscription_attributes.p256dh' => 'nullable|string',
            'subscription_attributes.auth' => 'nullable|string',
        ]);

        $user = auth()->user();

        // Check if subscription already exists based on subscription type
        $query = $user->notificationSubscriptions()
            ->where('subscription_type', $validated['subscription_type']);
        
        // For FCM subscriptions, check by push_token
        if ($validated['subscription_type'] === 'fcm' && !empty($validated['subscription_attributes']['push_token'])) {
            $query->whereJsonContains('subscription_attributes->push_token', $validated['subscription_attributes']['push_token']);
        }
        // For browser push subscriptions, check by endpoint
        elseif ($validated['subscription_type'] === 'browser_push' && !empty($validated['subscription_attributes']['endpoint'])) {
            $query->whereJsonContains('subscription_attributes->endpoint', $validated['subscription_attributes']['endpoint']);
        }
        
        $existingSubscription = $query->first();

        if ($existingSubscription) {
            $existingSubscription->update([
                'subscription_attributes' => $validated['subscription_attributes'],
            ]);

            return response()->json($existingSubscription);
        }

        // Create new subscription
        $subscription = $user->notificationSubscriptions()->create([
            'subscription_type' => $validated['subscription_type'],
            'subscription_attributes' => $validated['subscription_attributes'],
        ]);

        return response()->json($subscription, 201);
    }

    /**
     * Delete a notification subscription.
     * DELETE /api/v1/notification_subscriptions
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'push_token' => 'required|string',
        ]);

        $user = auth()->user();

        $subscription = $user->notificationSubscriptions()
            ->whereJsonContains('subscription_attributes->push_token', $validated['push_token'])
            ->first();

        if ($subscription) {
            $subscription->delete();
        }

        return response()->json(null, 200);
    }
}
