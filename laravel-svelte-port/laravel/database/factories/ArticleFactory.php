<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Article;
use App\Models\Category;
use App\Models\Portal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(6);

        return [
            'account_id' => Account::factory(),
            'portal_id' => Portal::factory(),
            'category_id' => null,
            'folder_id' => null,
            'author_id' => User::factory(),
            'associated_article_id' => null,
            'title' => $title,
            'slug' => time().'-'.str($title)->slug(),
            'content' => fake()->paragraphs(5, true),
            'description' => fake()->optional()->sentence(),
            'status' => Article::STATUS_PUBLISHED,
            'position' => fake()->numberBetween(1, 100),
            'views' => fake()->numberBetween(0, 10000),
            'locale' => 'en',
            'meta' => null,
        ];
    }

    /**
     * Create a draft article.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Article::STATUS_DRAFT,
        ]);
    }

    /**
     * Create a published article.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Article::STATUS_PUBLISHED,
        ]);
    }

    /**
     * Create an archived article.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Article::STATUS_ARCHIVED,
        ]);
    }

    /**
     * Create an article with a category.
     */
    public function withCategory(): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => Category::factory(),
        ]);
    }

    /**
     * Create an article with many views.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'views' => fake()->numberBetween(5000, 100000),
        ]);
    }

    /**
     * Create an article in a specific locale.
     */
    public function inLocale(string $locale): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => $locale,
        ]);
    }
}
