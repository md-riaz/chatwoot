<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AgentBot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AgentBot>
 */
class AgentBotFactory extends Factory
{
    protected $model = AgentBot::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'name' => fake()->firstName().' Bot',
            'description' => fake()->optional()->sentence(),
            'outgoing_url' => fake()->url(),
            'bot_type' => AgentBot::TYPE_WEBHOOK,
            'bot_config' => null,
        ];
    }

    /**
     * Create a system bot (no account).
     */
    public function systemBot(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_id' => null,
        ]);
    }

    /**
     * Create a webhook bot.
     */
    public function webhook(): static
    {
        return $this->state(fn (array $attributes) => [
            'bot_type' => AgentBot::TYPE_WEBHOOK,
            'outgoing_url' => fake()->url(),
        ]);
    }

    /**
     * Create a bot with avatar.
     */
    public function withAvatar(): static
    {
        return $this->state(fn (array $attributes) => [
            // Avatar will be handled by HasAvatar trait, not as a database column
        ]);
    }
}
