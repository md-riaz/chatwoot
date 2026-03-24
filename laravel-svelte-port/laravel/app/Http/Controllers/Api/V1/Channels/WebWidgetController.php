<?php

namespace App\Http\Controllers\Api\V1\Channels;

use App\Http\Controllers\Controller;
use App\Http\Resources\Inbox\InboxResource;
use App\Models\Account;
use App\Models\Inbox;
use App\Models\Channels\WebWidget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebWidgetController extends Controller
{
    /**
     * Create a Web Widget channel.
     */
    public function create(Request $request, Account $account): JsonResponse
    {
        abort_unless($request->user()?->isAdministratorOf($account), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'website_url' => 'required|url',
            'welcome_title' => 'nullable|string',
            'welcome_tagline' => 'nullable|string',
            'widget_color' => 'nullable|string|max:7',
            'reply_time' => 'nullable|integer|min:0',
            'pre_chat_form_enabled' => 'boolean',
            'pre_chat_form_options' => 'nullable|array',
            'hmac_mandatory' => 'boolean',
            'continuity_via_email' => 'boolean',
            'allowed_domains' => 'nullable|string',
            'greeting_enabled' => 'boolean',
            'greeting_message' => 'nullable|string',
            'enable_auto_assignment' => 'boolean',
            'working_hours_enabled' => 'boolean',
            'timezone' => 'nullable|string|timezone',
        ]);

        $channel = WebWidget::create([
            'account_id' => $account->id,
            'website_url' => $validated['website_url'],
            'website_token' => Str::uuid()->toString(),
            'widget_color' => $validated['widget_color'] ?? '#1f93ff',
            'welcome_title' => $validated['welcome_title'] ?? null,
            'welcome_tagline' => $validated['welcome_tagline'] ?? null,
            'pre_chat_form_enabled' => $validated['pre_chat_form_enabled'] ?? false,
            'pre_chat_form_options' => $validated['pre_chat_form_options'] ?? [],
            'reply_time' => $validated['reply_time'] ?? 0,
            'hmac_token' => Str::random(32),
            'hmac_mandatory' => $validated['hmac_mandatory'] ?? false,
            'continuity_via_email' => $validated['continuity_via_email'] ?? true,
            'allowed_domains' => $validated['allowed_domains'] ?? null,
        ]);

        $inbox = Inbox::create([
            'name' => $validated['name'],
            'account_id' => $account->id,
            'channel_type' => 'Channel::WebWidget',
            'channel_id' => $channel->id,
            'enable_auto_assignment' => $validated['enable_auto_assignment'] ?? true,
            'greeting_enabled' => $validated['greeting_enabled'] ?? false,
            'greeting_message' => $validated['greeting_message'] ?? null,
            'working_hours_enabled' => $validated['working_hours_enabled'] ?? false,
            'timezone' => $validated['timezone'] ?? config('app.timezone'),
        ]);

        return (new InboxResource($inbox->load('channel')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update Web Widget channel settings.
     */
    public function update(Request $request, Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);
        abort_unless($inbox->channel_type === 'Channel::WebWidget', 400);
        abort_unless($request->user()?->isAdministratorOf($account), 403);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'website_url' => 'url',
            'welcome_title' => 'nullable|string',
            'welcome_tagline' => 'nullable|string',
            'widget_color' => 'nullable|string|max:7',
            'reply_time' => 'nullable|integer|min:0',
            'pre_chat_form_enabled' => 'boolean',
            'pre_chat_form_options' => 'nullable|array',
            'hmac_mandatory' => 'boolean',
            'continuity_via_email' => 'boolean',
            'allowed_domains' => 'nullable|string',
        ]);

        $inbox->update(['name' => $validated['name'] ?? $inbox->name]);

        if ($inbox->channel instanceof WebWidget) {
            $inbox->channel->update(array_filter([
                'website_url' => $validated['website_url'] ?? null,
                'welcome_title' => $validated['welcome_title'] ?? null,
                'welcome_tagline' => $validated['welcome_tagline'] ?? null,
                'widget_color' => $validated['widget_color'] ?? null,
                'reply_time' => $validated['reply_time'] ?? null,
                'pre_chat_form_enabled' => $validated['pre_chat_form_enabled'] ?? null,
                'pre_chat_form_options' => $validated['pre_chat_form_options'] ?? null,
                'hmac_mandatory' => $validated['hmac_mandatory'] ?? null,
                'continuity_via_email' => $validated['continuity_via_email'] ?? null,
                'allowed_domains' => $validated['allowed_domains'] ?? null,
            ], fn ($value) => $value !== null));
        }

        return (new InboxResource($inbox->fresh()->load('channel')))
            ->response()
            ->setStatusCode(200);
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
                  window.clearlineSDK.run({
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
