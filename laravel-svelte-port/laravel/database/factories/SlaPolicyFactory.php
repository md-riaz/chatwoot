<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\SlaPolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

class SlaPolicyFactory extends Factory
{
    protected $model = SlaPolicy::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'name' => $this->faker->words(3, true) . ' SLA',
            'description' => $this->faker->optional()->sentence(),
            'first_response_time_threshold' => $this->faker->randomElement([300, 600, 1800, 3600, 7200]),
            'next_response_time_threshold' => $this->faker->randomElement([1800, 3600, 7200, 14400]),
            'resolution_time_threshold' => $this->faker->randomElement([14400, 28800, 86400, 172800]),
            'only_during_business_hours' => $this->faker->boolean(30),
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'active' => false,
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Urgent SLA',
            'first_response_time_threshold' => 300, // 5 minutes
            'next_response_time_threshold' => 900, // 15 minutes
            'resolution_time_threshold' => 3600, // 1 hour
        ]);
    }

    public function standard(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Standard SLA',
            'first_response_time_threshold' => 3600, // 1 hour
            'next_response_time_threshold' => 7200, // 2 hours
            'resolution_time_threshold' => 86400, // 24 hours
        ]);
    }
}
