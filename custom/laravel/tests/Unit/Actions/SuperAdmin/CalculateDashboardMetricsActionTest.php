<?php

namespace Tests\Unit\Actions\SuperAdmin;

use Tests\TestCase;
use App\Actions\SuperAdmin\CalculateDashboardMetricsAction;
use App\Data\SuperAdmin\DashboardData;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CalculateDashboardMetricsActionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_dashboard_data_object()
    {
        $result = CalculateDashboardMetricsAction::run();

        $this->assertInstanceOf(DashboardData::class, $result);
    }

    /** @test */
    public function it_has_correct_structure()
    {
        $result = CalculateDashboardMetricsAction::run();

        $this->assertObjectHasProperty('accountsCount', $result);
        $this->assertObjectHasProperty('usersCount', $result);
        $this->assertObjectHasProperty('inboxesCount', $result);
        $this->assertObjectHasProperty('conversationsCount', $result);
        $this->assertObjectHasProperty('chartData', $result);
    }

    /** @test */
    public function it_formats_numbers_as_strings()
    {
        $result = CalculateDashboardMetricsAction::run();

        $this->assertIsString($result->accountsCount);
        $this->assertIsString($result->usersCount);
        $this->assertIsString($result->inboxesCount);
        $this->assertIsString($result->conversationsCount);
    }

    /** @test */
    public function it_returns_chart_data_as_array()
    {
        $result = CalculateDashboardMetricsAction::run();

        $this->assertIsArray($result->chartData);
        
        // Chart data should be array of [date, count] pairs
        if (!empty($result->chartData)) {
            $firstItem = $result->chartData[0];
            $this->assertIsArray($firstItem);
            $this->assertCount(2, $firstItem);
            $this->assertIsString($firstItem[0]); // date
            $this->assertIsInt($firstItem[1]); // count
        }
    }

    /** @test */
    public function it_can_be_converted_to_array()
    {
        $result = CalculateDashboardMetricsAction::run();
        $array = $result->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('accountsCount', $array);
        $this->assertArrayHasKey('usersCount', $array);
        $this->assertArrayHasKey('inboxesCount', $array);
        $this->assertArrayHasKey('conversationsCount', $array);
        $this->assertArrayHasKey('chartData', $array);
    }
}