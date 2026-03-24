<?php

namespace App\Http\Controllers\Api\V1\Channels;

use App\Http\Controllers\Controller;
use App\Http\Resources\Inbox\InboxResource;
use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Services\Channels\Facebook\FacebookService;
use App\Services\Channels\Facebook\FacebookGraphClient;
use App\Jobs\Channels\ProcessFacebookWebhookJob;

class FacebookController extends Controller
{
    public function __construct(
        private FacebookGraphClient $facebookGraphClient
    ) {}

    /**
     * Create a Facebook channel.
     */
    public function create(Request $request, Account $account): JsonResponse
    {
        abort_unless($request->user()?->isAdministratorOf($account), 403);

        $validated = $request->validate([
            'page_access_token' => 'required|string',
            'page_id' => 'required|string',
            'name' => 'required|string|max:255',
            'user_access_token' => 'required|string',
        ]);

        $existingInbox = Inbox::query()
            ->where('account_id', $account->id)
            ->where('channel_type', 'Channel::FacebookPage')
            ->whereHasMorph('channel', [\App\Models\Channels\FacebookPage::class], function ($query) use ($validated) {
                $query->where('page_id', $validated['page_id']);
            })
            ->first();

        if ($existingInbox) {
            abort(422, 'A Facebook inbox for this page already exists.');
        }

        // Create channel record for Facebook page
        $fb = \App\Models\Channels\FacebookPage::create([
            'account_id' => $account->id,
            'page_id' => $validated['page_id'],
            'page_access_token' => $validated['page_access_token'],
            'user_access_token' => $validated['user_access_token'] ?? null,
        ]);

        // Create the inbox and associate the channel
        $inbox = Inbox::create([
            'name' => $validated['name'],
            'account_id' => $account->id,
            'channel_type' => 'Channel::FacebookPage',
            'channel_id' => $fb->id,
        ]);

        try {
            $this->facebookGraphClient->subscribePage(
                $validated['page_id'],
                $validated['page_access_token']
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to subscribe Facebook page during inbox creation', [
                'account_id' => $account->id,
                'page_id' => $validated['page_id'],
                'error' => $e->getMessage(),
            ]);
        }

        return (new InboxResource($inbox->load('channel')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update Facebook channel settings.
     */
    public function update(Request $request, Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);
        abort_unless($inbox->channel_type === 'Channel::FacebookPage', 400);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'page_access_token' => 'string',
        ]);

        $inbox->update(['name' => $validated['name'] ?? $inbox->name]);

        return response()->json(['data' => $inbox]);
    }

    /**
     * Receive webhook from Facebook.
     */
    public function webhook(Request $request): JsonResponse
    {
        // Verify X-Hub-Signature (sha256) if present
        $appSecret = config('services.facebook.app_secret');
        $signature = $request->header('X-Hub-Signature-256') ?? $request->header('X-Hub-Signature');
        $raw = $request->getContent();

        if ($signature && $appSecret) {
            // signature format: sha256=hex
            if (str_starts_with($signature, 'sha256=')) {
                $hash = substr($signature, 7);
                $expected = hash_hmac('sha256', $raw, $appSecret);
                if (! hash_equals($expected, $hash)) {
                    Log::warning('Facebook webhook signature mismatch');
                    return response()->json(['error' => 'invalid signature'], 401);
                }
            }
        }

        $payload = json_decode($raw, true);
        if (! is_array($payload)) {
            return response()->json(['error' => 'invalid payload'], 400);
        }

        $service = new FacebookService();

        // Dispatch processing job with raw payload; the job will delegate to the service.
        try {
            ProcessFacebookWebhookJob::dispatch($payload);
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch ProcessFacebookWebhookJob', ['error' => $e->getMessage()]);
        }

        return response()->json(['status' => 'queued']);
    }

    /**
     * Verify webhook (for Facebook verification).
     */
    public function verifyWebhook(Request $request): mixed
    {
        $mode = $request->get('hub.mode');
        $token = $request->get('hub.verify_token');
        $challenge = $request->get('hub.challenge');

        if ($mode === 'subscribe' && $token === config('services.facebook.verify_token')) {
            return response($challenge, 200);
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }

    /**
     * Initiate Facebook OAuth flow.
     */
    public function initiateAuthorization(Request $request, Account $account): JsonResponse
    {
        abort_unless($request->user()?->isAdministratorOf($account), 403);

        $state = Str::random(40);
        Cache::put(
            "facebook_oauth_state:{$state}",
            [
                'account_id' => $account->id,
                'user_id' => $request->user()->id,
            ],
            now()->addMinutes(10)
        );

        $redirectUri = route('facebook.oauth.callback');
        $appId = config('services.facebook.app_id', '');

        $oauthUrl = "https://www.facebook.com/v18.0/dialog/oauth?" . http_build_query([
            'client_id' => $appId,
            'redirect_uri' => $redirectUri,
            'scope' => 'pages_show_list,pages_messaging,pages_manage_metadata',
            'response_type' => 'code',
            'state' => $state,
        ]);

        return response()->json([
            'authorization_url' => $oauthUrl,
        ]);
    }

    public function oauthCallback(Request $request): RedirectResponse
    {
        $state = $request->string('state')->toString();
        $code = $request->string('code')->toString();
        $error = $request->string('error')->toString();

        $statePayload = $state !== ''
            ? Cache::pull("facebook_oauth_state:{$state}")
            : null;

        if (! is_array($statePayload) || empty($statePayload['account_id']) || empty($statePayload['user_id'])) {
            return redirect('/app?facebook_auth=error&reason=invalid_state');
        }

        $redirectBase = "/app/accounts/{$statePayload['account_id']}/settings/inboxes/new/facebook";

        if ($error !== '' || $code === '') {
            return redirect("{$redirectBase}?facebook_auth=error&reason=authorization_failed");
        }

        try {
            $tokenResponse = Http::acceptJson()
                ->timeout(15)
                ->get(rtrim(config('services.facebook.graph_url', 'https://graph.facebook.com'), '/') . '/' . trim(config('services.facebook.graph_version', 'v18.0'), '/') . '/oauth/access_token', [
                    'client_id' => config('services.facebook.app_id'),
                    'client_secret' => config('services.facebook.app_secret'),
                    'redirect_uri' => route('facebook.oauth.callback'),
                    'code' => $code,
                ])
                ->throw()
                ->json();

            $userAccessToken = $tokenResponse['access_token'] ?? null;

            if (! is_string($userAccessToken) || $userAccessToken === '') {
                throw new \RuntimeException('Facebook did not return an access token.');
            }

            $tokenKey = Str::random(48);
            Cache::put(
                "facebook_oauth_token:{$tokenKey}",
                [
                    'account_id' => $statePayload['account_id'],
                    'user_id' => $statePayload['user_id'],
                    'user_access_token' => $userAccessToken,
                ],
                now()->addMinutes(10)
            );

            return redirect("{$redirectBase}?facebook_auth=success&token_key={$tokenKey}");
        } catch (\Throwable $e) {
            Log::warning('Facebook OAuth callback failed', [
                'account_id' => $statePayload['account_id'],
                'user_id' => $statePayload['user_id'],
                'error' => $e->getMessage(),
            ]);

            return redirect("{$redirectBase}?facebook_auth=error&reason=token_exchange_failed");
        }
    }

    /**
     * Get Facebook pages for authorization.
     */
    public function pages(Request $request, Account $account): JsonResponse
    {
        abort_unless($request->user()?->isAdministratorOf($account), 403);

        $userAccessToken = $request->string('user_access_token')->toString();

        if ($userAccessToken === '') {
            return response()->json(['data' => []]);
        }

        $existingPageIds = \App\Models\Channels\FacebookPage::query()
            ->where('account_id', $account->id)
            ->pluck('page_id')
            ->map(fn ($pageId) => (string) $pageId)
            ->all();

        $pages = $this->facebookGraphClient
            ->getUserPages($userAccessToken)
            ->map(function (array $page) use ($existingPageIds, $userAccessToken) {
                return [
                    'id' => $page['id'],
                    'name' => $page['name'],
                    'page_access_token' => $page['page_access_token'],
                    'user_access_token' => $userAccessToken,
                    'instagram_id' => $page['instagram_id'],
                    'exists' => in_array($page['id'], $existingPageIds, true),
                ];
            })
            ->values();

        return response()->json(['data' => $pages]);
    }

    public function consumeCallbackToken(Request $request, Account $account): JsonResponse
    {
        abort_unless($request->user()?->isAdministratorOf($account), 403);

        $validated = $request->validate([
            'token_key' => 'required|string',
        ]);

        $payload = Cache::pull("facebook_oauth_token:{$validated['token_key']}");

        if (! is_array($payload)) {
            abort(404, 'Facebook authorization token was not found or has expired.');
        }

        if ((int) $payload['account_id'] !== $account->id || (int) $payload['user_id'] !== $request->user()->id) {
            abort(403, 'This Facebook authorization token does not belong to the current user.');
        }

        return response()->json([
            'user_access_token' => $payload['user_access_token'],
        ]);
    }
}
