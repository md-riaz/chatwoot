<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inbox>
 */
class InboxFactory extends Factory
{
    protected $model = Inbox::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'name' => fake()->words(2, true).' Inbox',
            'channel_type' => 'App\Models\Channels\WebWidget',
            'channel_id' => null,
            'enable_auto_assignment' => fake()->boolean(80),
            'greeting_enabled' => fake()->boolean(30),
            'greeting_message' => fake()->optional()->sentence(),
            'enable_email_collect' => true,
            'csat_survey_enabled' => fake()->boolean(20),
            'allow_messages_after_resolved' => true,
            'timezone' => fake()->timezone(),
            'working_hours_enabled' => false,
            'out_of_office_message' => null,
        ];
    }

    /**
     * Indicate that auto assignment is enabled.
     */
    public function withAutoAssignment(): static
    {
        return $this->state(fn (array $attributes) => [
            'enable_auto_assignment' => true,
        ]);
    }

    /**
     * Indicate that auto assignment is disabled.
     */
    public function withoutAutoAssignment(): static
    {
        return $this->state(fn (array $attributes) => [
            'enable_auto_assignment' => false,
        ]);
    }
}
