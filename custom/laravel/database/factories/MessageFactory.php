<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'conversation_id' => Conversation::factory(),
            'inbox_id' => Inbox::factory(),
            'sender_id' => null,
            'sender_type' => null,
            'message_type' => fake()->randomElement([
                Message::TYPE_INCOMING,
                Message::TYPE_OUTGOING,
            ]),
            'content' => fake()->paragraph(),
            'content_attributes' => null,
            'content_type' => Message::CONTENT_TEXT,
            'status' => Message::STATUS_SENT,
            'private' => false,
            'external_source_id' => null,
            'external_source_ids' => null,
            'source_id' => null,
        ];
    }

    /**
     * Indicate that the message is incoming.
     */
    public function incoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => Message::TYPE_INCOMING,
        ]);
    }

    /**
     * Indicate that the message is outgoing.
     */
    public function outgoing(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => Message::TYPE_OUTGOING,
        ]);
    }

    /**
     * Indicate that the message is a private note.
     */
    public function privateNote(): static
    {
        return $this->state(fn (array $attributes) => [
            'private' => true,
            'message_type' => Message::TYPE_OUTGOING,
        ]);
    }

    /**
     * Indicate that the message is an activity.
     */
    public function activity(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => Message::TYPE_ACTIVITY,
            'content' => fake()->randomElement([
                'Conversation was resolved',
                'Conversation was reopened',
                'Agent was assigned',
            ]),
        ]);
    }
}
