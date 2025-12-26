<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\CannedResponse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CannedResponse>
 */
class CannedResponseFactory extends Factory
{
    protected $model = CannedResponse::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'short_code' => fake()->unique()->slug(2),
            'content' => fake()->paragraph(),
        ];
    }

    /**
     * Create a greeting canned response.
     */
    public function greeting(): static
    {
        return $this->state(fn (array $attributes) => [
            'short_code' => 'greeting',
            'content' => 'Hello! Thank you for reaching out. How can I help you today?',
        ]);
    }

    /**
     * Create a farewell canned response.
     */
    public function farewell(): static
    {
        return $this->state(fn (array $attributes) => [
            'short_code' => 'farewell',
            'content' => 'Thank you for contacting us. Have a great day!',
        ]);
    }
}
