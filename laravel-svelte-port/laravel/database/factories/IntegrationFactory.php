<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Integration;
use Illuminate\Database\Eloquent\Factories\Factory;

class IntegrationFactory extends Factory
{
    protected $model = Integration::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'type' => $this->faker->randomElement(['slack', 'linear', 'dialogflow', 'shopify']),
            'settings' => [],
            'credentials' => [],
            'active' => true,
        ];
    }

    public function slack(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'slack',
            'settings' => [
                'channel_id' => 'C' . $this->faker->regexify('[A-Z0-9]{10}'),
                'channel_name' => '#' . $this->faker->slug(2),
            ],
            'credentials' => [
                'bot_token' => 'xoxb-' . $this->faker->regexify('[0-9]{12}-[0-9]{12}-[a-zA-Z0-9]{24}'),
            ],
        ]);
    }

    public function linear(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'linear',
            'settings' => [
                'team_id' => $this->faker->uuid(),
            ],
            'credentials' => [
                'api_key' => 'lin_api_' . $this->faker->regexify('[a-zA-Z0-9]{32}'),
            ],
        ]);
    }

    public function dialogflow(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'dialogflow',
            'settings' => [
                'project_id' => $this->faker->slug(2) . '-' . $this->faker->randomNumber(6),
            ],
            'credentials' => [
                'service_account_json' => json_encode([
                    'type' => 'service_account',
                    'project_id' => $this->faker->slug(2),
                    'private_key' => 'mock_private_key',
                    'client_email' => $this->faker->email(),
                ]),
            ],
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'active' => false,
        ]);
    }
}
