<?php

namespace App\Services\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class RetryableHttpClient
{
    public static function post(string $url, array $options = [], int $maxAttempts = 3)
    {
        $attempt = 0;
        $client = new Client(array_merge(['timeout' => 15], $options['client'] ?? []));

        while (++$attempt <= $maxAttempts) {
            try {
                $resp = $client->post($url, $options);

                // handle 429
                $status = $resp->getStatusCode();
                if ($status === 429) {
                    $retryAfter = (int) $resp->getHeaderLine('Retry-After');
                    $wait = $retryAfter > 0 ? $retryAfter : (2 ** $attempt);
                    sleep($wait);
                    continue;
                }

                return $resp;
            } catch (RequestException $e) {
                $response = $e->getResponse();
                if ($response && $response->getStatusCode() === 429) {
                    $retryAfter = (int) $response->getHeaderLine('Retry-After');
                    $wait = $retryAfter > 0 ? $retryAfter : (2 ** $attempt);
                    sleep($wait);
                    continue;
                }

                if ($attempt >= $maxAttempts) {
                    throw $e;
                }

                // exponential backoff
                sleep(2 ** $attempt);
            }
        }

        throw new \RuntimeException('Failed after retries');
    }
}
