<?php

namespace App\Http\Controllers\Api\V1\Widget;

use App\Models\Inbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfigsController extends BaseController
{
    /**
     * Get widget configuration for an inbox.
     * POST /api/v1/widget/config
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'website_token' => 'required|string',
        ]);

        $inbox = Inbox::where('channel_type', 'Channel::WebWidget')
            ->whereHas('channel', function ($query) use ($validated) {
                $query->where('website_token', $validated['website_token']);
            })
            ->with(['channel', 'account'])
            ->first();

        if (!$inbox) {
            return response()->json(['error' => 'Invalid website token'], 404);
        }

        $webWidget = $inbox->channel;
        $account = $inbox->account;

        return response()->json([
            'websocket_host' => config('reverb.servers.reverb.host'),
            'locale' => $account->locale ?? 'en',
            'channel_config' => [
                'auth_token' => $webWidget->website_token ?? '',
                'website_name' => $webWidget->website_name ?? $inbox->name,
                'website_url' => $webWidget->website_url ?? '',
                'widget_color' => $webWidget->widget_color ?? '#1f93ff',
                'enabled_features' => $webWidget->enabled_features ?? [
                    'attachments' => true,
                    'emoji_picker' => true,
                ],
                'enabled_languages' => $webWidget->enabled_languages ?? [],
                'reply_time' => $webWidget->reply_time ?? 'in_a_few_hours',
                'pre_chat_form_enabled' => $webWidget->pre_chat_form_enabled ?? false,
                'pre_chat_form_options' => $webWidget->pre_chat_form_options ?? [],
                'working_hours_enabled' => $inbox->working_hours_enabled ?? false,
                'working_hours' => $inbox->workingHours ?? [],
                'out_of_office_message' => $inbox->out_of_office_message ?? '',
                'csat_survey_enabled' => $inbox->csat_survey_enabled ?? false,
                'welcome_title' => $webWidget->welcome_title ?? '',
                'welcome_tagline' => $webWidget->welcome_tagline ?? '',
            ],
            'contact' => null, // Will be set when contact authenticates
        ]);
    }
}
