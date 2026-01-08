<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Inbox;
use App\Models\Webhook;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Webhook>
 */
class WebhookFactory extends Factory
{
    protected $model = Webhook::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'inbox_id' => null,
            'url' => fake()->url(),
            'subscriptions' => fake()->randomElements([
                'conversation_created',
                'conversation_status_changed',
                'conversation_updated',
                'message_created',
                'message_updated',
                'webwidget_triggered',
            ], fake()->numberBetween(1, 6)),
        ];
    }

    /**
     * Indicate that the webhook is for a specific inbox.
     */
    public function forInbox(): static
    {
        return $this->state(fn (array $attributes) => [
            'inbox_id' => Inbox::factory(),
        ]);
    }

    /**
     * Indicate all subscriptions are enabled.
     */
    public function allSubscriptions(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscriptions' => [
                'conversation_created',
                'conversation_status_changed',
                'conversation_updated',
                'message_created',
                'message_updated',
                'webwidget_triggered',
            ],
        ]);
    }
}
