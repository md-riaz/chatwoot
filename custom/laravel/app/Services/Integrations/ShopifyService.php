<?php

namespace App\Services\Integrations;

use App\Models\Integration;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

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

    public function getCustomerByEmail(string $email): array
    {
        if (empty($this->baseUri()) || empty($this->accessToken())) {
            return [];
        }

        $resp = $this->client->get("admin/api/2023-10/customers/search.json", [
            'query' => ['query' => "email:{$email}"]
        ]);

        return json_decode((string) $resp->getBody(), true) ?: [];
    }

    public function getOrdersByCustomerId(string $customerId): array
    {
        if (empty($this->baseUri()) || empty($this->accessToken())) {
            return [];
        }

        $resp = $this->client->get("admin/api/2023-10/customers/{$customerId}/orders.json");

        return json_decode((string) $resp->getBody(), true) ?: [];
    }

    public function getOrder(string $orderId): array
    {
        if (empty($this->baseUri()) || empty($this->accessToken())) {
            return [];
        }

        $resp = $this->client->get("admin/api/2023-10/orders/{$orderId}.json");

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
