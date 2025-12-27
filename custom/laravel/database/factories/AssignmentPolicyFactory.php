<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AssignmentPolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssignmentPolicy>
 */
class AssignmentPolicyFactory extends Factory
{
    protected $model = AssignmentPolicy::class;

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
            'assignment_order' => fake()->randomElement([0, 1]),
            'conversation_priority' => fake()->randomElement([0, 1]),
            'fair_distribution_limit' => fake()->numberBetween(10, 100),
            'fair_distribution_window' => fake()->randomElement([1800, 3600, 7200]),
            'enabled' => true,
        ];
    }

    /**
     * Indicate that the policy is disabled.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enabled' => false,
        ]);
    }
}
