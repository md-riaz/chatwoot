<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Category;
use App\Models\Portal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'account_id' => Account::factory(),
            'portal_id' => Portal::factory(),
            'parent_category_id' => null,
            'name' => ucfirst($name),
            'slug' => str($name)->slug(),
            'description' => fake()->optional()->sentence(),
            'position' => fake()->numberBetween(1, 100),
            'locale' => 'en',
            'icon' => fake()->optional()->randomElement(['book', 'help-circle', 'settings', 'users', 'file-text']),
        ];
    }

    /**
     * Create a category with a parent.
     */
    public function withParent(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_category_id' => Category::factory(),
        ]);
    }

    /**
     * Create a root category (no parent).
     */
    public function root(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_category_id' => null,
        ]);
    }

    /**
     * Create a category in a specific locale.
     */
    public function inLocale(string $locale): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => $locale,
        ]);
    }
}
