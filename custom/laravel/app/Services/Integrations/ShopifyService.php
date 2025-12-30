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
}
