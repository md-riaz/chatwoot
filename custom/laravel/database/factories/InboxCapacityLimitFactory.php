<?php

namespace Database\Factories;

use App\Models\AgentCapacityPolicy;
use App\Models\Inbox;
use App\Models\InboxCapacityLimit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InboxCapacityLimit>
 */
class InboxCapacityLimitFactory extends Factory
{
    protected $model = InboxCapacityLimit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agent_capacity_policy_id' => AgentCapacityPolicy::factory(),
            'inbox_id' => Inbox::factory(),
            'conversation_limit' => fake()->numberBetween(1, 20),
        ];
    }

    /**
     * Set a specific conversation limit.
     */
    public function withLimit(int $limit): static
    {
        return $this->state(fn (array $attributes) => [
            'conversation_limit' => $limit,
        ]);
    }

    /**
     * Set a low capacity limit.
     */
    public function lowCapacity(): static
    {
        return $this->state(fn (array $attributes) => [
            'conversation_limit' => fake()->numberBetween(1, 3),
        ]);
    }

    /**
     * Set a high capacity limit.
     */
    public function highCapacity(): static
    {
        return $this->state(fn (array $attributes) => [
            'conversation_limit' => fake()->numberBetween(15, 50),
        ]);
    }
}