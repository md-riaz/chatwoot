<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'user_id' => User::factory(),
            'notification_type' => fake()->randomElement([
                'conversation_assignment',
                'conversation_creation',
                'message_created',
                'conversation_mention',
                'assigned_conversation_new_message',
            ]),
            'primary_actor_type' => 'User',
            'primary_actor_id' => User::factory(),
            'secondary_actor_type' => null,
            'secondary_actor_id' => null,
            'read_at' => null,
            'snoozed_until' => null,
            'meta' => [
                'title' => fake()->sentence(),
                'message' => fake()->paragraph(),
            ],
        ];
    }

    /**
     * Indicate that the notification is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => now(),
        ]);
    }

    /**
     * Indicate that the notification is snoozed.
     */
    public function snoozed(): static
    {
        return $this->state(fn (array $attributes) => [
            'snoozed_until' => now()->addHours(2),
        ]);
    }
}