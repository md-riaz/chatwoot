<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AgentCapacityPolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AgentCapacityPolicy>
 */
class AgentCapacityPolicyFactory extends Factory
{
    protected $model = AgentCapacityPolicy::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'exclusion_rules' => [],
        ];
    }
}
