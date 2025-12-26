<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Inbox;
use App\Models\WorkingHour;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkingHoursController extends Controller
{
    /**
     * Display working hours for an inbox.
     */
    public function index(Account $account, Inbox $inbox): JsonResource
    {
        abort_unless($inbox->account_id === $account->id, 404);

        $workingHours = WorkingHour::where('inbox_id', $inbox->id)
            ->orderBy('day_of_week')
            ->get();

        return JsonResource::collection($workingHours);
    }

    /**
     * Update working hours for an inbox.
     */
    public function update(Request $request, Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);

        $validated = $request->validate([
            'working_hours' => 'required|array',
            'working_hours.*.day_of_week' => 'required|integer|between:0,6',
            'working_hours.*.closed_all_day' => 'boolean',
            'working_hours.*.open_hour' => 'nullable|integer|between:0,23',
            'working_hours.*.open_minutes' => 'nullable|integer|between:0,59',
            'working_hours.*.close_hour' => 'nullable|integer|between:0,23',
            'working_hours.*.close_minutes' => 'nullable|integer|between:0,59',
            'working_hours.*.open_all_day' => 'boolean',
        ]);

        // Delete existing working hours
        WorkingHour::where('inbox_id', $inbox->id)->delete();

        // Create new working hours
        foreach ($validated['working_hours'] as $hours) {
            WorkingHour::create([
                'inbox_id' => $inbox->id,
                'account_id' => $account->id,
                ...$hours,
            ]);
        }

        $workingHours = WorkingHour::where('inbox_id', $inbox->id)
            ->orderBy('day_of_week')
            ->get();

        return response()->json(['data' => $workingHours]);
    }

    /**
     * Get account-level working hours settings.
     */
    public function accountSettings(Account $account): JsonResponse
    {
        // Get account working hours settings
        $settings = [
            'timezone' => $account->timezone ?? 'UTC',
            'working_hours_enabled' => $account->working_hours_enabled ?? false,
            'out_of_office_message' => $account->out_of_office_message ?? '',
        ];

        return response()->json(['data' => $settings]);
    }

    /**
     * Update account-level working hours settings.
     */
    public function updateAccountSettings(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'timezone' => 'string|timezone',
            'working_hours_enabled' => 'boolean',
            'out_of_office_message' => 'nullable|string',
        ]);

        $account->update($validated);

        return response()->json(['data' => $validated]);
    }

    /**
     * Check if inbox is currently open.
     */
    public function isOpen(Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);

        $now = now()->setTimezone($account->timezone ?? 'UTC');
        $dayOfWeek = $now->dayOfWeek;
        $currentTime = $now->format('H:i');

        $workingHour = WorkingHour::where('inbox_id', $inbox->id)
            ->where('day_of_week', $dayOfWeek)
            ->first();

        $isOpen = false;

        if ($workingHour) {
            if ($workingHour->open_all_day) {
                $isOpen = true;
            } elseif (!$workingHour->closed_all_day) {
                $openTime = sprintf('%02d:%02d', $workingHour->open_hour, $workingHour->open_minutes);
                $closeTime = sprintf('%02d:%02d', $workingHour->close_hour, $workingHour->close_minutes);
                $isOpen = $currentTime >= $openTime && $currentTime <= $closeTime;
            }
        }

        return response()->json([
            'data' => [
                'is_open' => $isOpen,
                'current_time' => $currentTime,
                'timezone' => $account->timezone ?? 'UTC',
            ]
        ]);
    }
}
