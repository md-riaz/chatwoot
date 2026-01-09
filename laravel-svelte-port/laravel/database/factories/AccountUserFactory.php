<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccountUser>
 */
class AccountUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'user_id' => User::factory(),
            'role' => fake()->randomElement(['administrator', 'agent']),
            'availability' => fake()->randomElement([0, 1, 2]), // Use integer values for enum
            'active_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the user is an administrator.
     */
    public function administrator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'administrator',
        ]);
    }

    /**
     * Indicate that the user is an agent.
     */
    public function agent(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'agent',
        ]);
    }

    /**
     * Indicate that the user is online.
     */
    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'availability' => 1, // Use integer value
        ]);
    }

    /**
     * Indicate that the user is offline.
     */
    public function offline(): static
    {
        return $this->state(fn (array $attributes) => [
            'availability' => 0, // Use integer value
        ]);
    }
}