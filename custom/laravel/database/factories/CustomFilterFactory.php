<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\CustomFilter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomFilter>
 */
class CustomFilterFactory extends Factory
{
    protected $model = CustomFilter::class;

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
            'name' => fake()->words(2, true).' Filter',
            'filter_type' => fake()->randomElement([
                CustomFilter::TYPE_CONVERSATION,
                CustomFilter::TYPE_CONTACT,
                CustomFilter::TYPE_REPORT,
            ]),
            'query' => [
                [
                    'attribute_key' => 'status',
                    'filter_operator' => 'equal_to',
                    'values' => ['open'],
                    'query_operator' => 'and',
                ],
            ],
        ];
    }

    /**
     * Create a conversation filter.
     */
    public function conversation(): static
    {
        return $this->state(fn (array $attributes) => [
            'filter_type' => CustomFilter::TYPE_CONVERSATION,
            'query' => [
                [
                    'attribute_key' => 'status',
                    'filter_operator' => 'equal_to',
                    'values' => ['open'],
                    'query_operator' => 'and',
                ],
            ],
        ]);
    }

    /**
     * Create a contact filter.
     */
    public function contact(): static
    {
        return $this->state(fn (array $attributes) => [
            'filter_type' => CustomFilter::TYPE_CONTACT,
            'query' => [
                [
                    'attribute_key' => 'email',
                    'filter_operator' => 'contains',
                    'values' => ['@example.com'],
                    'query_operator' => 'and',
                ],
            ],
        ]);
    }

    /**
     * Create a report filter.
     */
    public function report(): static
    {
        return $this->state(fn (array $attributes) => [
            'filter_type' => CustomFilter::TYPE_REPORT,
            'query' => [
                [
                    'attribute_key' => 'date_range',
                    'filter_operator' => 'equal_to',
                    'values' => ['last_7_days'],
                    'query_operator' => 'and',
                ],
            ],
        ]);
    }
}
