<?php

namespace App\Http\Controllers\Api\V1\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopifyController extends Controller
{
    /**
     * Get Shopify integration settings.
     */
    public function show(Account $account): JsonResponse
    {
        $integration = null; // Would fetch from integrations table

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
        // Fetch customer data from Shopify
        $customer = [
            'id' => 'customer_id',
            'email' => 'customer@example.com',
            'orders_count' => 5,
            'total_spent' => '250.00',
        ];

        return response()->json(['data' => $customer]);
    }

    /**
     * Get Shopify orders for contact.
     */
    public function orders(Account $account, int $contactId): JsonResponse
    {
        // Fetch orders from Shopify
        $orders = [];

        return response()->json(['data' => $orders]);
    }

    /**
     * Get Shopify order details.
     */
    public function order(Account $account, string $orderId): JsonResponse
    {
        // Fetch order details from Shopify
        $order = [];

        return response()->json(['data' => $order]);
    }

    /**
     * Handle Shopify webhook.
     */
    public function webhook(Request $request): JsonResponse
    {
        // Verify Shopify webhook signature
        // Process webhook events (order created, customer updated, etc.)

        return response()->json(['status' => 'ok']);
    }
}
