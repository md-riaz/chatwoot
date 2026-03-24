<?php

namespace App\Services\Channels\Facebook;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;

class FacebookGraphClient
{
    public function __construct(
        private HttpFactory $http
    ) {}

    public function getUserPages(string $userAccessToken): Collection
    {
        $response = $this->request()
            ->get('/me/accounts', [
                'access_token' => $userAccessToken,
                'fields' => 'id,name,access_token,instagram_business_account{id}',
            ])
            ->throw()
            ->json('data', []);

        return collect($response)->map(function (array $page) {
            return [
                'id' => (string) ($page['id'] ?? ''),
                'name' => (string) ($page['name'] ?? ''),
                'page_access_token' => $page['access_token'] ?? null,
                'instagram_id' => data_get($page, 'instagram_business_account.id'),
            ];
        })->filter(fn (array $page) => $page['id'] !== '' && $page['name'] !== '')->values();
    }

    public function subscribePage(string $pageId, string $pageAccessToken): array
    {
        return $this->request()
            ->post("/{$pageId}/subscribed_apps", [
                'subscribed_fields' => implode(',', [
                    'messages',
                    'messaging_postbacks',
                    'messaging_optins',
                    'message_deliveries',
                    'message_reads',
                    'messaging_account_linking',
                ]),
                'access_token' => $pageAccessToken,
            ])
            ->throw()
            ->json();
    }

    private function request()
    {
        $baseUrl = rtrim(config('services.facebook.graph_url', 'https://graph.facebook.com'), '/');
        $version = trim(config('services.facebook.graph_version', 'v18.0'), '/');

        return $this->http
            ->baseUrl("{$baseUrl}/{$version}")
            ->acceptJson()
            ->asForm()
            ->timeout(15)
            ->retry(3, 500, function ($exception) {
                return $exception instanceof RequestException
                    && in_array($exception->response?->status(), [408, 429, 500, 502, 503, 504], true);
            });
    }
}
