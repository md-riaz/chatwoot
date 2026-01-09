<?php

namespace Database\Factories\Channels;

use App\Models\Account;
use App\Models\Channels\Sms;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmsFactory extends Factory
{
    protected $model = Sms::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'phone_number' => $this->faker->e164PhoneNumber(),
            'provider' => 'bandwidth',
            'provider_config' => [
                'account_id' => $this->faker->uuid(),
                'api_key' => $this->faker->uuid(),
                'api_secret' => $this->faker->uuid(),
                'application_id' => $this->faker->uuid(),
            ],
        ];
    }

    public function bandwidth(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'bandwidth',
        ]);
    }

    public function withCompleteConfig(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider_config' => [
                'account_id' => $this->faker->uuid(),
                'api_key' => $this->faker->uuid(),
                'api_secret' => $this->faker->uuid(),
                'application_id' => $this->faker->uuid(),
            ],
        ]);
    }
}