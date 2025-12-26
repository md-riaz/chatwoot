<?php

namespace App\Http\Controllers\Api\V1\Channels;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebWidgetController extends Controller
{
    /**
     * Create a Web Widget channel.
     */
    public function create(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'website_url' => 'required|url',
            'welcome_title' => 'nullable|string',
            'welcome_tagline' => 'nullable|string',
            'widget_color' => 'nullable|string|max:7',
            'reply_time' => 'nullable|string|in:in_a_few_minutes,in_a_few_hours,in_a_day',
            'pre_chat_form_enabled' => 'boolean',
            'pre_chat_form_options' => 'nullable|array',
        ]);

        // Create the inbox with Web Widget channel
        $inbox = Inbox::create([
            'name' => $validated['name'],
            'account_id' => $account->id,
            'channel_type' => 'Channel::WebWidget',
        ]);

        return response()->json(['data' => $inbox], 201);
    }

    /**
     * Update Web Widget channel settings.
     */
    public function update(Request $request, Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);
        abort_unless($inbox->channel_type === 'Channel::WebWidget', 400);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'website_url' => 'url',
            'welcome_title' => 'nullable|string',
            'welcome_tagline' => 'nullable|string',
            'widget_color' => 'nullable|string|max:7',
            'reply_time' => 'nullable|string|in:in_a_few_minutes,in_a_few_hours,in_a_day',
            'pre_chat_form_enabled' => 'boolean',
            'pre_chat_form_options' => 'nullable|array',
            'hmac_mandatory' => 'boolean',
            'continuity_via_email' => 'boolean',
        ]);

        $inbox->update(['name' => $validated['name'] ?? $inbox->name]);

        return response()->json(['data' => $inbox]);
    }

    /**
     * Get widget script for embedding.
     */
    public function script(Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);
        abort_unless($inbox->channel_type === 'Channel::WebWidget', 400);

        $script = sprintf(
            '<script>
              (function(d,t) {
                var BASE_URL="%s";
                var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
                g.src=BASE_URL+"/packs/js/sdk.js";
                g.defer = true;
                g.async = true;
                s.parentNode.insertBefore(g,s);
                g.onload=function(){
                  window.chatwootSDK.run({
                    websiteToken: "%s",
                    baseUrl: BASE_URL
                  })
                }
              })(document,"script");
            </script>',
            config('app.url'),
            $inbox->channel->website_token ?? 'token'
        );

        return response()->json(['data' => ['script' => $script]]);
    }
}
