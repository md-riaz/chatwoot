<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

/**
 * Page crawler for fetching metadata (title, description, images).
 * Caches results for a configurable TTL.
 * Enterprise-grade features (rate-limiting, concurrency) can be added later.
 * @see enterprise/app/services/page_crawler_service.rb
 */
class PageCrawlerService
{
    public function fetchMetadata(string $url): array
    {
        $cacheKey = 'pagecrawler:' . md5($url);
        $ttl = config('crawler.ttl', 3600);

        return Cache::remember($cacheKey, $ttl, function () use ($url) {
            try {
                $response = Http::timeout(5)->get($url);
            } catch (\Throwable $e) {
                return ['status' => 0, 'title' => null, 'description' => null, 'images' => [], 'url' => $url];
            }

            if (! $response->successful()) {
                return ['status' => $response->status(), 'title' => null, 'description' => null, 'images' => [], 'url' => $url];
            }

            $html = $response->body();

            libxml_use_internal_errors(true);
            $doc = new \DOMDocument();
            $doc->loadHTML($html);

            $title = null;
            $titleNodes = $doc->getElementsByTagName('title');
            if ($titleNodes->length) {
                $title = trim($titleNodes->item(0)->textContent);
            }

            $description = null;
            $images = [];

            $metas = $doc->getElementsByTagName('meta');
            foreach ($metas as $meta) {
                $name = strtolower($meta->getAttribute('name'));
                $prop = strtolower($meta->getAttribute('property'));

                if ($name === 'description' && ! $description) {
                    $description = $meta->getAttribute('content');
                }

                if ($prop === 'og:description' && ! $description) {
                    $description = $meta->getAttribute('content');
                }

                if ($prop === 'og:image') {
                    $images[] = $meta->getAttribute('content');
                }
            }

            $imgTags = $doc->getElementsByTagName('img');
            foreach ($imgTags as $img) {
                $src = $img->getAttribute('src');
                if ($src) {
                    $images[] = $src;
                }
            }

            $images = array_values(array_unique(array_filter($images)));

            return [
                'status' => 200,
                'title' => $title,
                'description' => $description,
                'images' => $images,
                'url' => $url,
            ];
        });
    }
}
