<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'name' => fake()->name(),
            'email' => fake()->optional(0.8)->safeEmail(),
            'phone_number' => fake()->optional(0.5)->phoneNumber(),
            'identifier' => fake()->optional()->uuid(),
            'avatar_url' => fake()->optional()->imageUrl(200, 200, 'people'),
            'custom_attributes' => [],
            'additional_attributes' => [],
            'last_activity_at' => fake()->optional()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate that the contact has an email.
     */
    public function withEmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => fake()->safeEmail(),
        ]);
    }

    /**
     * Indicate that the contact has a phone number.
     */
    public function withPhone(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone_number' => fake()->phoneNumber(),
        ]);
    }
}
