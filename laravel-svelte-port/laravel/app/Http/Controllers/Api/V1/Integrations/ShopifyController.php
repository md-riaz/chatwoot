<?php

namespace App\Http\Controllers\Api\V1\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Integration;
use App\Services\Integrations\ShopifyService;
use App\Jobs\Integrations\ProcessShopifyWebhookJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;

class ShopifyController extends Controller
{
    /**
     * Get Shopify integration settings.
     */
    public function show(Account $account): JsonResponse
    {
        $integration = Integration::ofType('shopify')->where('account_id', $account->id)->first();

        return response()->json(['data' => $integration]);
    }

    /**
     * Create/Connect Shopify integration.
     */
    public function create(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'shop_domain' => 'required|string',
            'access_token' => 'required|string',
        ]);

        $integration = Integration::updateOrCreate(
            ['account_id' => $account->id, 'type' => 'shopify'],
            [
                'settings' => [
                    'shop_domain' => $validated['shop_domain'],
                    'api_version' => $request->input('api_version', '2023-10'),
                ],
                'credentials' => [
                    'access_token' => $validated['access_token'],
                    'webhook_secret' => $request->input('webhook_secret') ?? null,
                ],
                'active' => true,
            ]
        );

        return response()->json(['data' => $integration], 201);
    }

    /**
     * Update Shopify integration settings.
     */
    public function update(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'enabled' => 'boolean',
        ]);

        $integration = Integration::ofType('shopify')->where('account_id', $account->id)->firstOrFail();

        if (array_key_exists('enabled', $validated)) {
            $integration->active = (bool) $validated['enabled'];
        }

        if ($request->filled('webhook_secret')) {
            $creds = $integration->credentials ?? [];
            $creds['webhook_secret'] = $request->input('webhook_secret');
            $integration->credentials = $creds;
        }

        $integration->save();

        return response()->json(['data' => $integration]);
    }

    /**
     * Disconnect Shopify integration.
     */
    public function destroy(Account $account): JsonResponse
    {
        $integration = Integration::ofType('shopify')->where('account_id', $account->id)->first();
        if ($integration) {
            $integration->delete();
        }

        return response()->json(null, 204);
    }

    /**
     * Get Shopify customer for contact.
     */
    public function customer(Account $account, int $contactId): JsonResponse
    {
        $integration = Integration::ofType('shopify')->where('account_id', $account->id)->firstOrFail();

        $shopify = new ShopifyService($integration);

        // In a real implementation we'd lookup contact and map to Shopify customer (e.g. by email)
        $contact = \App\Models\Contact::findOrFail($contactId);
        $result = $shopify->getCustomerByEmail($contact->email ?? '');

        return response()->json(['data' => $result]);
    }

    /**
     * Get Shopify orders for contact.
     */
    public function orders(Account $account, int $contactId): JsonResponse
    {
        $integration = Integration::ofType('shopify')->where('account_id', $account->id)->firstOrFail();
        $shopify = new ShopifyService($integration);

        $contact = \App\Models\Contact::findOrFail($contactId);
        $customers = $shopify->getCustomerByEmail($contact->email ?? '');

        // customers response shape: { customers: [ ... ] }
        $customerId = data_get($customers, 'customers.0.id');
        $orders = $customerId ? $shopify->getOrdersByCustomerId($customerId) : [];

        return response()->json(['data' => $orders]);
    }

    /**
     * Get Shopify order details.
     */
    public function order(Account $account, string $orderId): JsonResponse
    {
        $integration = Integration::ofType('shopify')->where('account_id', $account->id)->firstOrFail();
        $shopify = new ShopifyService($integration);

        $order = $shopify->getOrder($orderId);

        return response()->json(['data' => $order]);
    }

    /**
     * Handle Shopify webhook.
     */
    public function webhook(Request $request): JsonResponse
    {
        $shop = Integration::ofType('shopify')->first();
        if (! $shop) {
            return response()->json(['error' => 'Shopify integration not configured'], 404);
        }

        $service = new ShopifyService($shop);
        if (! $service->verifyWebhookSignature($request)) {
            return response()->json(['error' => 'invalid signature'], 401);
        }

        $result = $service->processWebhook($request);

        // Dispatch a queued job to process the webhook payload asynchronously
        try {
            $integrationId = $shop->id ?? null;
            ProcessShopifyWebhookJob::dispatch($result['topic'] ?? 'unknown', $result['payload'] ?? [], $integrationId);
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch ProcessShopifyWebhookJob', ['error' => $e->getMessage()]);
        }

        return response()->json(['status' => 'ok', 'topic' => $result['topic'] ?? null]);
    }

    /**
     * Start OAuth install: generate state and redirect to Shopify authorize URL.
     */
    public function initiateAuthorization(Request $request)
    {
        $request->validate(['shop' => 'required|string']);

        $shop = $request->input('shop');
        $state = Str::random(40);

        // store state in cache for short time (5 minutes)
        Cache::put("shopify_oauth_state:{$state}", [
            'shop' => $shop,
            'account_id' => $request->input('account_id'),
        ], now()->addMinutes(5));

        $clientId = config('services.shopify.client_id');
        $scopes = config('services.shopify.scopes');
        $redirectUri = URL::to('/api/v1/callbacks/shopify/callback');

        $installUrl = "https://{$shop}/admin/oauth/authorize?client_id={$clientId}&scope={$scopes}&redirect_uri=".urlencode($redirectUri)."&state={$state}";

        return redirect()->away($installUrl);
    }

    /**
     * OAuth callback: validate state and exchange code for access token.
     */
    public function callback(Request $request)
    {
        $request->validate(['code' => 'required|string', 'state' => 'required|string', 'shop' => 'required|string']);

        $state = $request->input('state');
        $cached = Cache::pull("shopify_oauth_state:{$state}");
        if (! $cached) {
            return response()->json(['error' => 'invalid or expired state'], 403);
        }

        $shop = $request->input('shop');
        if (data_get($cached, 'shop') !== $shop) {
            return response()->json(['error' => 'shop mismatch'], 403);
        }

        $clientId = config('services.shopify.client_id');
        $clientSecret = config('services.shopify.client_secret');

        $response = Http::asForm()->post("https://{$shop}/admin/oauth/access_token", [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $request->input('code'),
        ]);

        if (! $response->ok()) {
            Log::error('Shopify access token exchange failed', ['status' => $response->status(), 'body' => $response->body()]);
            return response()->json(['error' => 'failed to fetch access token'], 500);
        }

        $body = $response->json();
        $accessToken = $body['access_token'] ?? null;
        if (! $accessToken) {
            return response()->json(['error' => 'no access token returned'], 500);
        }

        $accountId = data_get($cached, 'account_id');
        $attributes = ['type' => 'shopify'];
        if ($accountId) {
            $attributes['account_id'] = $accountId;
        }

        $integration = Integration::updateOrCreate(
            $attributes,
            [
                'settings' => [
                    'shop_domain' => $shop,
                    'api_version' => $request->input('api_version', Config::get('services.shopify.api_version', '2023-10')),
                ],
                'credentials' => [
                    'access_token' => $accessToken,
                ],
                'active' => true,
            ]
        );

        return response()->json(['data' => $integration]);
    }

    /**
     * Get Shopify products for the account
     */
    public function products(Account $account): JsonResponse
    {
        $integration = Integration::ofType('shopify')->where('account_id', $account->id)->firstOrFail();
        $shopify = new ShopifyService($integration);

        $limit = request()->input('limit', 50);
        $products = $shopify->fetchProducts($limit);

        return response()->json(['data' => $products]);
    }

    /**
     * Setup Shopify webhooks
     */
    public function setupWebhooks(Account $account): JsonResponse
    {
        $integration = Integration::ofType('shopify')->where('account_id', $account->id)->firstOrFail();
        $shopify = new ShopifyService($integration);

        $webhookUrl = URL::to('/api/v1/webhooks/shopify');
        $results = $shopify->setupWebhooks($webhookUrl);

        return response()->json(['data' => $results]);
    }

    /**
     * Test Shopify connection
     */
    public function testConnection(Account $account): JsonResponse
    {
        $integration = Integration::ofType('shopify')->where('account_id', $account->id)->firstOrFail();
        $shopify = new ShopifyService($integration);

        $isValid = $shopify->refreshTokenIfNeeded();

        return response()->json([
            'data' => [
                'connected' => $isValid,
                'shop_domain' => data_get($integration->settings, 'shop_domain'),
                'last_tested' => now()->toISOString(),
            ],
        ]);
    }
}
