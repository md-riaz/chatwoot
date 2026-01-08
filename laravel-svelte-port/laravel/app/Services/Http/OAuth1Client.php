<?php

namespace App\Services\Http;

/**
 * Minimal OAuth1 header generator for Twitter API requests.
 */
class OAuth1Client
{
    public static function buildAuthHeader(array $params): string
    {
        $parts = [];
        foreach ($params as $k => $v) {
            $parts[] = rawurlencode($k) . '="' . rawurlencode($v) . '"';
        }

        return 'OAuth ' . implode(', ', $parts);
    }

    public static function oauth1Header(string $method, string $url, array $oauth, array $body = []): string
    {
        // Build signature base string
        $params = $oauth;
        foreach ($body as $k => $v) {
            $params[$k] = $v;
        }

        ksort($params);

        $encodedParams = [];
        foreach ($params as $k => $v) {
            $encodedParams[] = rawurlencode($k) . '=' . rawurlencode($v);
        }

        $baseString = strtoupper($method) . '&' . rawurlencode($url) . '&' . rawurlencode(implode('&', $encodedParams));

        $signingKey = rawurlencode($oauth['consumer_secret']) . '&' . rawurlencode($oauth['token_secret'] ?? '');
        $signature = base64_encode(hash_hmac('sha1', $baseString, $signingKey, true));

        $oauthHeader = [
            'oauth_consumer_key' => $oauth['consumer_key'],
            'oauth_nonce' => $oauth['nonce'] ?? bin2hex(random_bytes(16)),
            'oauth_signature' => $signature,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => $oauth['timestamp'] ?? time(),
            'oauth_token' => $oauth['token'] ?? '',
            'oauth_version' => '1.0',
        ];

        return self::buildAuthHeader($oauthHeader);
    }
}
