<?php

namespace App\Services\Integrations\Shopify;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Integration\Hook;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyService
{
    private const REQUIRED_SCOPES = ['read_customers', 'read_orders', 'read_fulfillments'];

    public function __construct(
        private Account $account,
        private ?Hook $hook = null
    ) {
        $this->hook = $hook ?? $this->account->integrationHooks()->where('app_id', 'shopify')->first();
    }

    public function generateAuthUrl(string $shopDomain): array
    {
        $clientId = config('services.shopify.client_id');
        
        if (!$clientId) {
            return ['error' => 'Shopify client ID not configured'];
        }

        $state = $this->generateToken($this->account->id);
        $redirectUri = config('services.shopify.redirect_uri');

        $authUrl = "https://{$shopDomain}/admin/oauth/authorize?" . http_build_query([
            'client_id' => $clientId,
            'scope' => implode(',', self::REQUIRED_SCOPES),
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ]);

        return ['redirect_url' => $authUrl];
    }

    public function fetchCustomerOrders(Contact $contact): array
    {
        if (!$this->hook) {
            return ['error' => 'Shopify integration not configured'];
        }

        try {
            $customers = $this->fetchCustomers($contact);
            
            if (empty($customers)) {
                return ['orders' => []];
            }

            $orders = $this->fetchOrders($customers[0]['id']);
            
            return ['orders' => $orders];
        } catch (\Exception $e) {
            Log::error('Shopify API error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function generateToken(int $accountId): ?string
    {
        $clientSecret = config('services.shopify.client_secret');
        
        if (!$clientSecret) {
            return null;
        }

        try {
            return JWT::encode([
                'sub' => $accountId,
                'iat' => now()->timestamp,
            ], $clientSecret, 'HS256');
        } catch (\Exception $e) {
            Log::error('Failed to generate Shopify token: ' . $e->getMessage());
            return null;
        }
    }

    public function verifyToken(string $token): ?int
    {
        $clientSecret = config('services.shopify.client_secret');
        
        if (!$token || !$clientSecret) {
            return null;
        }

        try {
            $decoded = JWT::decode($token, new Key($clientSecret, 'HS256'));
            return $decoded->sub;
        } catch (\Exception $e) {
            Log::error('Failed to verify Shopify token: ' . $e->getMessage());
            return null;
        }
    }

    private function fetchCustomers(Contact $contact): array
    {
        $query = [];
        
        if ($contact->email) {
            $query[] = "email:{$contact->email}";
        }
        
        if ($contact->phone_number) {
            $query[] = "phone:{$contact->phone_number}";
        }

        if (empty($query)) {
            return [];
        }

        $response = $this->makeApiRequest('customers/search.json', [
            'query' => implode(' OR ', $query),
            'fields' => 'id,email,phone',
        ]);

        return $response['customers'] ?? [];
    }

    private function fetchOrders(string $customerId): array
    {
        $response = $this->makeApiRequest('orders.json', [
            'customer_id' => $customerId,
            'status' => 'any',
            'fields' => 'id,email,created_at,total_price,currency,fulfillment_status,financial_status',
        ]);

        $orders = $response['orders'] ?? [];

        // Add admin URL to each order
        return array_map(function ($order) {
            $order['admin_url'] = "https://{$this->hook->reference_id}/admin/orders/{$order['id']}";
            return $order;
        }, $orders);
    }

    private function makeApiRequest(string $endpoint, array $params = []): array
    {
        $url = "https://{$this->hook->reference_id}/admin/api/2025-01/{$endpoint}";
        
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->hook->access_token,
        ])->get($url, $params);

        if (!$response->successful()) {
            throw new \Exception("Shopify API error: {$response->status()} - {$response->body()}");
        }

        return $response->json();
    }
}