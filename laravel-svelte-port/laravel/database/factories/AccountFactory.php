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
            'feature_flags' => 32128855, // Default feature flags (binary combination)
            'limits' => [
                'agents' => 100,
                'inboxes' => 50,
            ],
            'status' => 0, // 0 = Active, 1 = Suspended
        ];
    }

    /**
     * Indicate that the account is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1, // 1 = Suspended
        ]);
    }
}
