<?php

namespace Tests\Unit\Services;

use App\Services\PageCrawlerService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PageCrawlerServiceTest extends TestCase
{
    public function test_fetch_metadata_parses_title_and_images()
    {
        Cache::forget('pagecrawler:' . md5('http://example.local/test'));

        $html = <<<'HTML'
<!doctype html>
<html>
<head>
<title>Test Page</title>
<meta name="description" content="A test page">
<meta property="og:image" content="https://example.local/og.jpg">
</head>
<body>
<img src="/image1.png" />
</body>
</html>
HTML;

        Http::fake([
            'http://example.local/test' => Http::response($html, 200),
        ]);

        $svc = new PageCrawlerService();
        $meta = $svc->fetchMetadata('http://example.local/test');

        $this->assertEquals(200, $meta['status']);
        $this->assertEquals('Test Page', $meta['title']);
        $this->assertContains('https://example.local/og.jpg', $meta['images']);
    }
}
