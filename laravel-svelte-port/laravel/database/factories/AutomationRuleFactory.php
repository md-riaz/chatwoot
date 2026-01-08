<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AutomationRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AutomationRule>
 */
class AutomationRuleFactory extends Factory
{
    protected $model = AutomationRule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventNames = [
            'conversation_created',
            'conversation_updated',
            'message_created',
            'conversation_opened',
        ];

        return [
            'account_id' => Account::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'event_name' => fake()->randomElement($eventNames),
            'conditions' => [
                [
                    'attribute_key' => 'status',
                    'filter_operator' => 'equal_to',
                    'values' => ['open'],
                    'query_operator' => 'and',
                ],
            ],
            'actions' => [
                [
                    'action_name' => 'add_label',
                    'action_params' => ['auto_tagged'],
                ],
            ],
            'active' => true,
        ];
    }

    /**
     * Create an active automation rule.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
        ]);
    }

    /**
     * Create an inactive automation rule.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    /**
     * Create a conversation created rule.
     */
    public function conversationCreated(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_name' => 'conversation_created',
        ]);
    }

    /**
     * Create a message created rule.
     */
    public function messageCreated(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_name' => 'message_created',
        ]);
    }

    /**
     * Create an auto-assign rule.
     */
    public function autoAssign(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_name' => 'conversation_created',
            'actions' => [
                [
                    'action_name' => 'assign_agent',
                    'action_params' => ['auto'],
                ],
            ],
        ]);
    }

    /**
     * Create an add label rule.
     */
    public function addLabel(string $label = 'priority'): static
    {
        return $this->state(fn (array $attributes) => [
            'actions' => [
                [
                    'action_name' => 'add_label',
                    'action_params' => [$label],
                ],
            ],
        ]);
    }
}
