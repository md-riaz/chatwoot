<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Segment;
use Illuminate\Database\Eloquent\Factories\Factory;

class SegmentFactory extends Factory
{
    protected $model = Segment::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'name' => $this->faker->words(2, true) . ' Segment',
            'description' => $this->faker->optional()->sentence(),
            'query' => [
                [
                    'attribute_key' => 'email',
                    'filter_operator' => 'is_present',
                    'values' => [],
                ],
            ],
        ];
    }

    public function recentContacts(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Recent Contacts',
            'query' => [
                [
                    'attribute_key' => 'created_at',
                    'filter_operator' => 'days_before',
                    'values' => [30],
                ],
            ],
        ]);
    }

    public function vipCustomers(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'VIP Customers',
            'query' => [
                [
                    'attribute_key' => 'custom_attributes',
                    'filter_operator' => 'contains',
                    'values' => ['vip'],
                ],
            ],
        ]);
    }
}
