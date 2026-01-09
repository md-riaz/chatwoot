<?php

namespace Database\Factories\Channels;

use App\Models\Account;
use App\Models\Channels\Voice;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoiceFactory extends Factory
{
    protected $model = Voice::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'phone_number' => $this->faker->unique()->e164PhoneNumber(),
            'provider' => 'twilio',
            'provider_config' => [
                'account_sid' => 'AC' . $this->faker->regexify('[a-f0-9]{32}'),
                'auth_token' => $this->faker->regexify('[a-f0-9]{32}'),
                'api_key_sid' => 'SK' . $this->faker->regexify('[a-f0-9]{32}'),
                'api_key_secret' => $this->faker->regexify('[a-zA-Z0-9]{32}'),
                'twiml_app_sid' => 'AP' . $this->faker->regexify('[a-f0-9]{32}'),
            ],
            'additional_attributes' => [],
        ];
    }

    /**
     * Configure for demo/seeding purposes.
     */
    public function demo(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'provider_config' => [
                    'account_sid' => 'demo_account_sid',
                    'auth_token' => 'demo_auth_token',
                    'api_key_sid' => 'demo_api_key_sid',
                    'api_key_secret' => 'demo_api_key_secret',
                    'twiml_app_sid' => 'demo_twiml_app_sid',
                ],
            ];
        });
    }
}