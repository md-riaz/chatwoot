<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Label;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Label>
 */
class LabelFactory extends Factory
{
    protected $model = Label::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'title' => fake()->unique()->word(),
            'description' => fake()->optional()->sentence(),
            'color' => fake()->hexColor(),
            'show_on_sidebar' => fake()->boolean(80),
        ];
    }

    /**
     * Indicate that the label should show on sidebar.
     */
    public function showOnSidebar(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_on_sidebar' => true,
        ]);
    }

    /**
     * Indicate that the label should not show on sidebar.
     */
    public function hideFromSidebar(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_on_sidebar' => false,
        ]);
    }
}
