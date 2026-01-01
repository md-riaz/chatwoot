<?php

namespace Database\Factories\Channels;

use App\Models\Account;
use App\Models\Channels\Whatsapp;
use Illuminate\Database\Eloquent\Factories\Factory;

class WhatsappFactory extends Factory
{
    protected $model = Whatsapp::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'phone_number' => $this->faker->phoneNumber(),
            'phone_number_id' => $this->faker->uuid(),
            'business_account_id' => $this->faker->uuid(),
            'provider' => Whatsapp::PROVIDER_CLOUD,
            'provider_config' => [
                'api_key' => $this->faker->uuid(),
                'phone_number_id' => $this->faker->uuid(),
                'business_account_id' => $this->faker->uuid(),
                'webhook_verify_token' => $this->faker->uuid(),
            ],
            'message_templates' => [],
            'message_templates_last_updated' => now(),
        ];
    }

    public function withCloudProvider(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => Whatsapp::PROVIDER_CLOUD,
        ]);
    }

    public function with360DialogProvider(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => Whatsapp::PROVIDER_DEFAULT,
        ]);
    }
}