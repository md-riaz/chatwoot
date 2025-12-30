<?php

namespace Tests\Unit\Services;

use App\Services\SearchService;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_matching_messages()
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite is not available in this environment');
        }

        $m1 = Message::factory()->create(['content' => 'Hello world']);
        $m2 = Message::factory()->create(['content' => 'Other message']);

        $svc = new SearchService();
        $results = $svc->search('Hello');

        $this->assertCount(1, $results);
        $this->assertEquals($m1->id, $results[0]['id']);
    }
}
