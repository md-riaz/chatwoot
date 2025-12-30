<?php

namespace App\Http\Controllers\Api\V1\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Integration;
use App\Services\Integrations\ShopifyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        // Store Shopify integration settings

        return response()->json(['message' => 'Shopify connected successfully'], 201);
    }

    /**
     * Update Shopify integration settings.
     */
    public function update(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'enabled' => 'boolean',
        ]);

        // Update Shopify integration settings

        return response()->json(['message' => 'Shopify settings updated']);
    }

    /**
     * Disconnect Shopify integration.
     */
    public function destroy(Account $account): JsonResponse
    {
        // Remove Shopify integration

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

        // Dispatch jobs or events based on $result['topic'] in a full implementation
        return response()->json(['status' => 'ok', 'topic' => $result['topic']]);
    }
}
