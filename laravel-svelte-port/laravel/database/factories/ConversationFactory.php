<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conversation>
 */
class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'inbox_id' => Inbox::factory(),
            'contact_id' => Contact::factory(),
            'contact_inbox_id' => null,
            'assignee_id' => null,
            'team_id' => null,
            'uuid' => fake()->uuid(),
            'display_id' => fake()->unique()->numberBetween(1, 100000),
            'status' => fake()->randomElement([
                Conversation::STATUS_OPEN,
                Conversation::STATUS_RESOLVED,
                Conversation::STATUS_PENDING,
            ]),
            'priority' => fake()->randomElement([
                Conversation::PRIORITY_NONE,
                Conversation::PRIORITY_LOW,
                Conversation::PRIORITY_MEDIUM,
                Conversation::PRIORITY_HIGH,
            ]),
            'additional_attributes' => null,
            'custom_attributes' => [],
            'first_reply_created_at' => null,
            'last_activity_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'waiting_since' => null,
            'snoozed_until' => null,
        ];
    }

    /**
     * Indicate that the conversation is open.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Conversation::STATUS_OPEN,
        ]);
    }

    /**
     * Indicate that the conversation is resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Conversation::STATUS_RESOLVED,
        ]);
    }

    /**
     * Indicate that the conversation is assigned.
     */
    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'assignee_id' => User::factory(),
        ]);
    }

    /**
     * Indicate that the conversation is unassigned.
     */
    public function unassigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'assignee_id' => null,
        ]);
    }
}
