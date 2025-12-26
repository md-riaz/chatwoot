<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'locale' => fake()->randomElement(['en', 'es', 'fr', 'de', 'pt']),
            'domain' => fake()->optional()->domainName(),
            'support_email' => fake()->companyEmail(),
            'settings' => [],
            'features' => [
                'inbound_emails' => true,
                'channel_email' => true,
                'channel_web_widget' => true,
            ],
            'limits' => [
                'agents' => 100,
                'inboxes' => 50,
            ],
            'status' => 1,
        ];
    }

    /**
     * Indicate that the account is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }
}
