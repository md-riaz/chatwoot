<?php

namespace Database\Factories\Channels;

use App\Models\Account;
use App\Models\Channels\Telegram;
use Illuminate\Database\Eloquent\Factories\Factory;

class TelegramFactory extends Factory
{
    protected $model = Telegram::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'bot_token' => $this->faker->numerify('##########') . ':' . $this->faker->regexify('[A-Za-z0-9_-]{35}'),
            'bot_name' => $this->faker->userName() . '_bot',
            'webhook_secret' => $this->faker->uuid(),
        ];
    }

    public function withCustomBotName(string $botName): static
    {
        return $this->state(fn (array $attributes) => [
            'bot_name' => $botName,
        ]);
    }
}