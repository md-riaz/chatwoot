<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Campaign;
use App\Models\Inbox;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Campaign>
 */
class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

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
            'sender_id' => null,
            'display_id' => fake()->unique()->numberBetween(1, 100000),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'message' => fake()->paragraph(),
            'campaign_type' => fake()->randomElement([
                Campaign::TYPE_ONGOING,
                Campaign::TYPE_ONE_OFF,
            ]),
            'campaign_status' => Campaign::STATUS_ACTIVE,
            'enabled' => true,
            'trigger_only_during_business_hours' => fake()->boolean(30),
            'scheduled_at' => null,
            'trigger_rules' => [],
            'audience' => [],
            'template_params' => null,
        ];
    }

    /**
     * Create an ongoing campaign.
     */
    public function ongoing(): static
    {
        return $this->state(fn (array $attributes) => [
            'campaign_type' => Campaign::TYPE_ONGOING,
        ]);
    }

    /**
     * Create a one-off campaign.
     */
    public function oneOff(): static
    {
        return $this->state(fn (array $attributes) => [
            'campaign_type' => Campaign::TYPE_ONE_OFF,
            'scheduled_at' => fake()->dateTimeBetween('now', '+7 days'),
        ]);
    }

    /**
     * Create an active campaign.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'campaign_status' => Campaign::STATUS_ACTIVE,
            'enabled' => true,
        ]);
    }

    /**
     * Create a completed campaign.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'campaign_status' => Campaign::STATUS_COMPLETED,
        ]);
    }

    /**
     * Create a disabled campaign.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enabled' => false,
        ]);
    }

    /**
     * Create a campaign with a sender.
     */
    public function withSender(): static
    {
        return $this->state(fn (array $attributes) => [
            'sender_id' => User::factory(),
        ]);
    }
}
