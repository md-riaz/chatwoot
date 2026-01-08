<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'name' => fake()->words(2, true).' Team',
            'description' => fake()->optional()->sentence(),
            'allow_auto_assign' => fake()->boolean(70),
        ];
    }

    /**
     * Indicate that auto-assign is enabled.
     */
    public function autoAssign(): static
    {
        return $this->state(fn (array $attributes) => [
            'allow_auto_assign' => true,
        ]);
    }

    /**
     * Indicate that auto-assign is disabled.
     */
    public function noAutoAssign(): static
    {
        return $this->state(fn (array $attributes) => [
            'allow_auto_assign' => false,
        ]);
    }
}
