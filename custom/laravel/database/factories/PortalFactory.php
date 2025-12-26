<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Portal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Portal>
 */
class PortalFactory extends Factory
{
    protected $model = Portal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'channel_web_widget_id' => null,
            'name' => fake()->company().' Help Center',
            'slug' => fake()->unique()->slug(2),
            'custom_domain' => null,
            'color' => fake()->hexColor(),
            'homepage_link' => fake()->optional()->url(),
            'page_title' => fake()->optional()->sentence(3),
            'header_text' => fake()->optional()->sentence(),
            'archived' => false,
            'config' => [
                'default_locale' => 'en',
                'allowed_locales' => ['en'],
            ],
            'ssl_settings' => null,
        ];
    }

    /**
     * Create a portal with custom domain.
     */
    public function withCustomDomain(): static
    {
        return $this->state(fn (array $attributes) => [
            'custom_domain' => fake()->domainName(),
        ]);
    }

    /**
     * Create an archived portal.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'archived' => true,
        ]);
    }

    /**
     * Create an active portal.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'archived' => false,
        ]);
    }

    /**
     * Create a multilingual portal.
     */
    public function multilingual(): static
    {
        return $this->state(fn (array $attributes) => [
            'config' => [
                'default_locale' => 'en',
                'allowed_locales' => ['en', 'es', 'fr', 'de'],
            ],
        ]);
    }
}
