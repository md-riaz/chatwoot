<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\NotificationSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationSettingsController extends Controller
{
    /**
     * Display the notification settings for the current user.
     */
    public function show(Account $account): JsonResponse
    {
        $user = auth()->user();

        $setting = NotificationSetting::firstOrCreate(
            [
                'account_id' => $account->id,
                'user_id' => $user->id,
            ],
            [
                'email_flags' => 0,
                'push_flags' => 0,
            ]
        );

        return response()->json([
            'data' => [
                'selected_email_flags' => $setting->selected_email_flags,
                'selected_push_flags' => $setting->selected_push_flags,
            ],
        ]);
    }

    /**
     * Update the notification settings for the current user.
     */
    public function update(Request $request, Account $account): JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'notification_settings.selected_email_flags' => 'nullable|array',
            'notification_settings.selected_email_flags.*' => 'string',
            'notification_settings.selected_push_flags' => 'nullable|array',
            'notification_settings.selected_push_flags.*' => 'string',
        ]);

        $setting = NotificationSetting::firstOrCreate(
            [
                'account_id' => $account->id,
                'user_id' => $user->id,
            ],
            [
                'email_flags' => 0,
                'push_flags' => 0,
            ]
        );

        $notificationSettings = $validated['notification_settings'] ?? [];

        if (isset($notificationSettings['selected_email_flags'])) {
            $setting->selected_email_flags = $notificationSettings['selected_email_flags'];
        }

        if (isset($notificationSettings['selected_push_flags'])) {
            $setting->selected_push_flags = $notificationSettings['selected_push_flags'];
        }

        $setting->save();

        return response()->json([
            'data' => [
                'selected_email_flags' => $setting->selected_email_flags,
                'selected_push_flags' => $setting->selected_push_flags,
            ],
        ]);
    }
}
