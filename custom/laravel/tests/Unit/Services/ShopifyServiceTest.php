<?php

namespace Tests\Unit\Services;

use App\Models\Integration;
use App\Services\Integrations\ShopifyService;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class ShopifyServiceTest extends TestCase
{
    public function test_verify_webhook_signature()
    {
        $secret = 'testsecret';
        $payload = json_encode(['id' => 123, 'topic' => 'orders/create']);

        $hmac = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        $integration = new Integration([
            'credentials' => ['webhook_secret' => $secret],
            'settings' => ['shop_domain' => 'example.myshopify.com'],
        ]);

        $service = new ShopifyService($integration);

        $request = Request::create('/shopify/webhook', 'POST', [], [], [], [], $payload);
        $request->headers->set('X-Shopify-Hmac-Sha256', $hmac);

        $this->assertTrue($service->verifyWebhookSignature($request));
    }
}
