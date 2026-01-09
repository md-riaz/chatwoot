<?php

namespace Database\Factories\Channels;

use App\Models\Account;
use App\Models\Channels\Api;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ApiFactory extends Factory
{
    protected $model = Api::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'webhook_url' => $this->faker->optional()->url(),
            'identifier' => Str::random(40),
            'hmac_token' => Str::random(40),
            'hmac_mandatory' => $this->faker->boolean(),
            'additional_attributes' => [
                'agent_reply_time_window' => $this->faker->optional()->numberBetween(1, 60),
            ],
        ];
    }

    public function withWebhook(): static
    {
        return $this->state(fn (array $attributes) => [
            'webhook_url' => $this->faker->url(),
        ]);
    }

    public function withoutWebhook(): static
    {
        return $this->state(fn (array $attributes) => [
            'webhook_url' => null,
        ]);
    }

    public function withHmac(): static
    {
        return $this->state(fn (array $attributes) => [
            'hmac_mandatory' => true,
        ]);
    }

    public function withoutHmac(): static
    {
        return $this->state(fn (array $attributes) => [
            'hmac_mandatory' => false,
        ]);
    }
}