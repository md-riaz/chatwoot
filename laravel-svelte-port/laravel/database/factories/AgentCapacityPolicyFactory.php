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

    /**
     * Set exclusion rules with overall capacity.
     */
    public function withOverallCapacity(int $capacity = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'exclusion_rules' => ['overall_capacity' => $capacity],
        ]);
    }

    /**
     * Set exclusion rules with excluded labels.
     */
    public function withExcludedLabels(array $labels = ['high-priority', 'vip']): static
    {
        return $this->state(fn (array $attributes) => [
            'exclusion_rules' => ['excluded_labels' => $labels],
        ]);
    }

    /**
     * Set exclusion rules with time-based exclusion.
     */
    public function withTimeExclusion(int $hours = 24): static
    {
        return $this->state(fn (array $attributes) => [
            'exclusion_rules' => ['exclude_older_than_hours' => $hours],
        ]);
    }

    /**
     * Set complex exclusion rules combining multiple criteria.
     */
    public function withComplexRules(): static
    {
        return $this->state(fn (array $attributes) => [
            'exclusion_rules' => [
                'excluded_labels' => ['high-priority', 'vip'],
                'exclude_older_than_hours' => 24,
                'overall_capacity' => 10,
            ],
        ]);
    }
}
