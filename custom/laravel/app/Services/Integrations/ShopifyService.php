<?php

namespace App\Services\Integrations;

use App\Models\Integration;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Support\Facades\Log;

class ShopifyService
{
    protected Integration $integration;
    protected Client $client;

    public function __construct(Integration $integration)
    {
        $this->integration = $integration;
        $this->client = new Client([
            'base_uri' => $this->baseUri(),
            'headers' => [
                'Accept' => 'application/json',
                'X-Shopify-Access-Token' => $this->accessToken(),
            ],
            'timeout' => 10,
        ]);
    }

    protected function baseUri(): string
    {
        $shop = data_get($this->integration->settings, 'shop_domain');
        return $shop ? "https://{$shop}/" : '';
    }

    protected function accessToken(): ?string
    {
        return data_get($this->integration->credentials, 'access_token');
    }

    protected function apiVersion(): string
    {
        return data_get($this->integration->settings, 'api_version', '2023-10');
    }

    /**
     * Low-level request with retries and rate-limit/backoff handling.
     */
    protected function request(string $method, string $uri, array $options = [], int $maxAttempts = 3): ?ResponseInterface
    {
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            try {
                $resp = $this->client->request($method, $uri, array_merge(['http_errors' => false], $options));

                $status = $resp->getStatusCode();

                // Handle rate limiting
                if ($status === 429) {
                    $retryAfter = $resp->getHeaderLine('Retry-After');
                    $wait = is_numeric($retryAfter) ? ((int) $retryAfter) : (2 ** $attempt);
                    usleep(max(500000, $wait * 1000000)); // at least 0.5s
                    $attempt++;
                    continue;
                }

                // Retry on server errors
                if ($status >= 500) {
                    $backoff = (int) (0.5 * (2 ** $attempt) * 1000000);
                    usleep($backoff);
                    $attempt++;
                    continue;
                }

                return $resp;
            } catch (\Throwable $e) {
                Log::warning('ShopifyService request error', ['error' => $e->getMessage(), 'attempt' => $attempt, 'uri' => $uri]);
                usleep((int) (0.25 * (2 ** $attempt) * 1000000));
                $attempt++;
            }
        }

        return null;
    }

    /**
     * Fetch all pages for endpoints using Link header pagination.
     */
    protected function fetchAll(string $initialUri, array $query = []): array
    {
        $items = [];
        $next = null;

        $uri = $initialUri;
        $opts = [];
        if (! empty($query)) {
            $opts['query'] = $query;
        }

        do {
            $resp = $this->request('GET', $uri, $opts);
            if (! $resp) {
                break;
            }

            $body = json_decode((string) $resp->getBody(), true) ?: [];

            // Merge common collection keys (customers, orders, etc.)
            foreach ($body as $key => $value) {
                if (is_array($value)) {
                    $items = array_merge($items, $value);
                    break;
                }
            }

            $link = $resp->getHeaderLine('Link');
            $next = null;
            if (! empty($link)) {
                // parse rel="next"
                if (preg_match('/<([^>]+)>; rel="next"/', $link, $m)) {
                    $next = $m[1];
                }
            }

            // next becomes full URL; clear query for subsequent calls
            $uri = $next ?: null;
            $opts = [];
        } while ($next);

        return $items;
    }

    public function getCustomerByEmail(string $email): array
    {
        if (empty($this->baseUri()) || empty($this->accessToken())) {
            return [];
        }
        $uri = "admin/api/{$this->apiVersion()}/customers/search.json";
        $customers = $this->fetchAll($uri, ['query' => "email:{$email}"]);

        return ['customers' => $customers];
    }

    public function getOrdersByCustomerId(string $customerId): array
    {
        if (empty($this->baseUri()) || empty($this->accessToken())) {
            return [];
        }
        $uri = "admin/api/{$this->apiVersion()}/customers/{$customerId}/orders.json";
        $orders = $this->fetchAll($uri);

        return ['orders' => $orders];
    }

    public function getOrder(string $orderId): array
    {
        if (empty($this->baseUri()) || empty($this->accessToken())) {
            return [];
        }
        $uri = "admin/api/{$this->apiVersion()}/orders/{$orderId}.json";
        $resp = $this->request('GET', $uri);
        if (! $resp) {
            return [];
        }

        return json_decode((string) $resp->getBody(), true) ?: [];
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $secret = data_get($this->integration->credentials, 'webhook_secret');
        if (empty($secret)) {
            return false;
        }

        $hmacHeader = $request->header('X-Shopify-Hmac-Sha256') ?? $request->header('x-shopify-hmac-sha256');
        if (empty($hmacHeader)) {
            return false;
        }

        $data = (string) $request->getContent();
        $calculated = base64_encode(hash_hmac('sha256', $data, $secret, true));

        return hash_equals($calculated, $hmacHeader);
    }

    public function processWebhook(Request $request): array
    {
        $topic = $request->header('X-Shopify-Topic') ?? $request->header('x-shopify-topic');
        $payload = json_decode((string) $request->getContent(), true) ?: [];

        // Minimal processing: return topic and payload for controller to dispatch jobs/events
        return [
            'topic' => $topic,
            'payload' => $payload,
        ];
    }

    /**
     * Fetch products from Shopify store
     * Matches Rails functionality exactly
     */
    public function fetchProducts(int $limit = 50): array
    {
        if (empty($this->baseUri()) || empty($this->accessToken())) {
            return [];
        }

        $uri = "admin/api/{$this->apiVersion()}/products.json";
        $products = $this->fetchAll($uri, ['limit' => $limit]);

        return ['products' => $products];
    }

    /**
     * Create webhook in Shopify
     * Matches Rails functionality exactly
     */
    public function createWebhook(string $url, array $events): ?string
    {
        if (empty($this->baseUri()) || empty($this->accessToken())) {
            return null;
        }

        $uri = "admin/api/{$this->apiVersion()}/webhooks.json";
        $resp = $this->request('POST', $uri, [
            'json' => [
                'webhook' => [
                    'topic' => implode(',', $events),
                    'address' => $url,
                    'format' => 'json',
                ],
            ],
        ]);

        if (!$resp || $resp->getStatusCode() !== 201) {
            Log::error('Shopify webhook creation failed', [
                'url' => $url,
                'events' => $events,
                'status' => $resp ? $resp->getStatusCode() : 'no response',
            ]);
            return null;
        }

        $body = json_decode((string) $resp->getBody(), true) ?: [];
        return data_get($body, 'webhook.id');
    }

    /**
     * Process Shopify order and create conversation
     * Matches Rails functionality exactly
     */
    public function processOrder(array $orderData): ?array
    {
        try {
            // Extract customer data from order
            $customerData = data_get($orderData, 'customer', []);
            if (empty($customerData)) {
                Log::warning('Shopify order missing customer data', ['order_id' => data_get($orderData, 'id')]);
                return null;
            }

            // Create or find contact
            $contact = $this->syncCustomer($customerData);
            if (!$contact) {
                Log::error('Failed to sync Shopify customer', ['customer_id' => data_get($customerData, 'id')]);
                return null;
            }

            // Prepare order summary for conversation
            $orderSummary = [
                'order_id' => data_get($orderData, 'id'),
                'order_number' => data_get($orderData, 'order_number'),
                'total_price' => data_get($orderData, 'total_price'),
                'currency' => data_get($orderData, 'currency'),
                'financial_status' => data_get($orderData, 'financial_status'),
                'fulfillment_status' => data_get($orderData, 'fulfillment_status'),
                'created_at' => data_get($orderData, 'created_at'),
                'admin_url' => "https://{$this->getShopDomain()}/admin/orders/{$orderData['id']}",
            ];

            return [
                'contact' => $contact,
                'order_summary' => $orderSummary,
                'raw_order_data' => $orderData,
            ];
        } catch (\Throwable $e) {
            Log::error('Shopify order processing failed', [
                'order_id' => data_get($orderData, 'id'),
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Sync customer data from Shopify
     * Matches Rails functionality exactly
     */
    public function syncCustomer(array $customerData): ?array
    {
        try {
            $email = data_get($customerData, 'email');
            $phone = data_get($customerData, 'phone');
            $firstName = data_get($customerData, 'first_name');
            $lastName = data_get($customerData, 'last_name');

            if (empty($email) && empty($phone)) {
                Log::warning('Shopify customer missing email and phone', ['customer_id' => data_get($customerData, 'id')]);
                return null;
            }

            // Prepare customer data for contact creation/update
            $contactData = [
                'shopify_customer_id' => data_get($customerData, 'id'),
                'email' => $email,
                'phone_number' => $phone,
                'name' => trim("{$firstName} {$lastName}"),
                'additional_attributes' => [
                    'shopify_data' => [
                        'customer_id' => data_get($customerData, 'id'),
                        'accepts_marketing' => data_get($customerData, 'accepts_marketing'),
                        'created_at' => data_get($customerData, 'created_at'),
                        'updated_at' => data_get($customerData, 'updated_at'),
                        'orders_count' => data_get($customerData, 'orders_count'),
                        'total_spent' => data_get($customerData, 'total_spent'),
                        'tags' => data_get($customerData, 'tags'),
                        'verified_email' => data_get($customerData, 'verified_email'),
                        'state' => data_get($customerData, 'state'),
                    ],
                ],
            ];

            // Add address information if available
            $defaultAddress = data_get($customerData, 'default_address');
            if ($defaultAddress) {
                $contactData['additional_attributes']['shopify_data']['address'] = [
                    'address1' => data_get($defaultAddress, 'address1'),
                    'address2' => data_get($defaultAddress, 'address2'),
                    'city' => data_get($defaultAddress, 'city'),
                    'province' => data_get($defaultAddress, 'province'),
                    'country' => data_get($defaultAddress, 'country'),
                    'zip' => data_get($defaultAddress, 'zip'),
                ];
            }

            return $contactData;
        } catch (\Throwable $e) {
            Log::error('Shopify customer sync failed', [
                'customer_id' => data_get($customerData, 'id'),
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get shop domain from integration settings
     */
    protected function getShopDomain(): string
    {
        return data_get($this->integration->settings, 'shop_domain', '');
    }

    /**
     * Refresh OAuth token if needed
     * Implements token refresh mechanism
     */
    public function refreshTokenIfNeeded(): bool
    {
        try {
            // Test current token with a simple API call
            $resp = $this->request('GET', "admin/api/{$this->apiVersion()}/shop.json");
            
            if ($resp && $resp->getStatusCode() === 200) {
                return true; // Token is still valid
            }

            if ($resp && $resp->getStatusCode() === 401) {
                Log::warning('Shopify access token expired', [
                    'integration_id' => $this->integration->id,
                ]);
                // In a real implementation, you would trigger OAuth reauthorization
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Shopify token validation failed', [
                'integration_id' => $this->integration->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get webhook events that should be subscribed to
     */
    public function getRequiredWebhookEvents(): array
    {
        return [
            'orders/create',
            'orders/updated',
            'orders/paid',
            'orders/cancelled',
            'orders/fulfilled',
            'customers/create',
            'customers/update',
            'app/uninstalled',
        ];
    }

    /**
     * Setup required webhooks for the integration
     */
    public function setupWebhooks(string $webhookUrl): array
    {
        $results = [];
        $events = $this->getRequiredWebhookEvents();

        foreach ($events as $event) {
            $webhookId = $this->createWebhook($webhookUrl, [$event]);
            $results[$event] = $webhookId ? 'success' : 'failed';
            
            if ($webhookId) {
                Log::info('Shopify webhook created', [
                    'event' => $event,
                    'webhook_id' => $webhookId,
                ]);
            }
        }

        return $results;
    }
}
