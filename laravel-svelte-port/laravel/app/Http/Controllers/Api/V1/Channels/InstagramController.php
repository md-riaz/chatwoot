<?php

namespace App\Http\Controllers\Api\V1\Channels;

use App\Http\Controllers\Controller;
use App\Http\Resources\Inbox\InboxResource;
use App\Models\Account;
use App\Models\Channels\Instagram;
use App\Models\Inbox;
use App\Jobs\Channels\ProcessInstagramWebhookJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\Channels\Instagram\InstagramGraphClient;

class InstagramController extends Controller
{
    public function __construct(
        private InstagramGraphClient $instagramGraphClient
    ) {}

    /**
     * Update an Instagram channel inbox.
     */
    public function update(Request $request, Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);
        abort_unless($inbox->channel_type === 'Channel::Instagram', 404);

        $validated = $request->validate([
            'access_token' => 'nullable|string',
            'expires_at' => 'nullable|date',
            'name' => 'string|max:255',
        ]);

        if (isset($validated['name'])) {
            $inbox->update(['name' => $validated['name']]);
        }

        $channelData = collect($validated)->only(['access_token', 'expires_at'])->filter()->toArray();
        if (! empty($channelData)) {
            $inbox->channel->update($channelData);
        }

        return response()->json(['data' => $inbox->fresh()->load('channel')]);
    }

    /**
     * Verify Instagram webhook.
     */
    public function verifyWebhook(Request $request): JsonResponse
    {
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        $verifyToken = config('services.instagram.verify_token');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            return response()->json((int) $challenge);
        }

        return response()->json(['error' => 'Invalid verification token'], 403);
    }

    /**
     * Handle Instagram webhook events.
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();

        ProcessInstagramWebhookJob::dispatch($payload);

        return response()->json(['success' => true]);
    }

    /**
     * Start Instagram OAuth flow.
     */
    public function initiateAuthorization(Request $request, Account $account): JsonResponse
    {
        abort_unless($request->user()?->isAdministratorOf($account), 403);

        $state = Str::random(40);
        Cache::put(
            "instagram_oauth_state:{$state}",
            [
                'account_id' => $account->id,
            ],
            now()->addMinutes(10)
        );

        return response()->json([
            'url' => $this->instagramGraphClient->authorizationUrl($state),
        ]);
    }

    public function oauthCallback(Request $request): RedirectResponse
    {
        $state = $request->string('state')->toString();
        $code = $request->string('code')->toString();
        $error = $request->string('error')->toString();

        $statePayload = $state !== ''
            ? Cache::pull("instagram_oauth_state:{$state}")
            : null;

        if (! is_array($statePayload) || empty($statePayload['account_id'])) {
            return redirect('/app?error_message=Invalid Instagram authorization state');
        }

        $accountId = (int) $statePayload['account_id'];
        $inboxPage = "/app/accounts/{$accountId}/settings/inboxes/new/instagram";

        if ($error !== '' || $code === '') {
            $message = $request->string('error_description')->toString() ?: 'Instagram authorization was denied.';
            return redirect($inboxPage . '?error_message=' . urlencode($message) . '&code=400');
        }

        try {
            $shortLivedToken = $this->instagramGraphClient->exchangeCodeForAccessToken($code);
            $longLivedToken = $this->instagramGraphClient->exchangeForLongLivedToken($shortLivedToken['access_token']);
            $userDetails = $this->instagramGraphClient->getUserDetails($longLivedToken['access_token']);

            $account = Account::findOrFail($accountId);
            $instagramId = (string) ($userDetails['user_id'] ?? '');
            $username = (string) ($userDetails['username'] ?? '');

            if ($instagramId === '' || $username === '') {
                throw new \RuntimeException('Instagram user details were incomplete.');
            }

            $channel = Instagram::where('account_id', $account->id)
                ->where('instagram_id', $instagramId)
                ->first();

            $alreadyExists = (bool) $channel;
            $expiresAt = now()->addSeconds((int) ($longLivedToken['expires_in'] ?? 0));

            if ($channel) {
                $channel->update([
                    'access_token' => $longLivedToken['access_token'],
                    'expires_at' => $expiresAt,
                ]);
                $channel->inbox?->update(['name' => $username]);
            } else {
                $channel = Instagram::create([
                    'account_id' => $account->id,
                    'instagram_id' => $instagramId,
                    'access_token' => $longLivedToken['access_token'],
                    'expires_at' => $expiresAt,
                ]);

                $channel->subscribe();

                Inbox::create([
                    'account_id' => $account->id,
                    'name' => $username,
                    'channel_type' => 'Channel::Instagram',
                    'channel_id' => $channel->id,
                ]);
            }

            $inbox = $channel->fresh()->inbox;

            if (! $inbox) {
                throw new \RuntimeException('Instagram inbox was not created.');
            }

            if ($alreadyExists) {
                return redirect("/app/accounts/{$accountId}/settings/inboxes/{$inbox->id}/configuration");
            }

            return redirect("/app/accounts/{$accountId}/settings/inboxes/new/{$inbox->id}/agents");
        } catch (\Throwable $e) {
            Log::warning('Instagram OAuth callback failed', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);

            return redirect($inboxPage . '?error_message=' . urlencode($e->getMessage()) . '&code=500');
        }
    }
}
