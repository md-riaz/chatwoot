<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'name' => fake()->company(),
            'domain' => fake()->optional(0.7)->domainName(),
            'description' => fake()->optional(0.5)->paragraph(),
            'contacts_count' => 0,
        ];
    }

    /**
     * Company with a specific domain.
     */
    public function withDomain(string $domain): static
    {
        return $this->state(fn (array $attributes) => [
            'domain' => $domain,
        ]);
    }

    /**
     * Company without a domain.
     */
    public function withoutDomain(): static
    {
        return $this->state(fn (array $attributes) => [
            'domain' => null,
        ]);
    }
}
