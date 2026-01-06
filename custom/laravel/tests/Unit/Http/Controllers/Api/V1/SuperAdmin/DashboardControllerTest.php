<?php

namespace Tests\Unit\Http\Controllers\Api\V1\SuperAdmin;

use Tests\TestCase;
use App\Http\Controllers\Api\V1\SuperAdmin\DashboardController;
use App\Data\SuperAdmin\DashboardData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class DashboardControllerTest extends TestCase
{
    /** @test */
    public function it_returns_dashboard_metrics_in_correct_format()
    {
        // Mock the cache to return a DashboardData object
        $mockData = new DashboardData(
            accountsCount: '1,234',
            usersCount: '5,678',
            inboxesCount: '90',
            conversationsCount: '12,345',
            chartData: [
                ['2024-01-01', 10],
                ['2024-01-02', 15],
                ['2024-01-03', 8],
            ]
        );

        Cache::shouldReceive('remember')
            ->once()
            ->with('super_admin_dashboard_metrics', 300, \Closure::class)
            ->andReturn($mockData);

        $controller = new DashboardController();
        $response = $controller->index();

        $this->assertInstanceOf(JsonResponse::class, $response);
        
        $responseData = $response->getData(true);
        
        // Verify the response has data wrapper (consistent with other SuperAdmin APIs)
        $this->assertArrayHasKey('data', $responseData);
        $data = $responseData['data'];
        
        // Verify the response structure matches Rails format
        $this->assertArrayHasKey('accountsCount', $data);
        $this->assertArrayHasKey('usersCount', $data);
        $this->assertArrayHasKey('inboxesCount', $data);
        $this->assertArrayHasKey('conversationsCount', $data);
        $this->assertArrayHasKey('chartData', $data);
        
        // Verify data types
        $this->assertIsString($data['accountsCount']);
        $this->assertIsString($data['usersCount']);
        $this->assertIsString($data['inboxesCount']);
        $this->assertIsString($data['conversationsCount']);
        $this->assertIsArray($data['chartData']);
        
        // Verify specific values
        $this->assertEquals('1,234', $data['accountsCount']);
        $this->assertEquals('5,678', $data['usersCount']);
        $this->assertEquals('90', $data['inboxesCount']);
        $this->assertEquals('12,345', $data['conversationsCount']);
        
        // Verify chart data structure
        $this->assertCount(3, $data['chartData']);
        $this->assertEquals(['2024-01-01', 10], $data['chartData'][0]);
    }
}