<?php

namespace Database\Factories\Channels;

use App\Models\Account;
use App\Models\Channels\FacebookPage;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacebookPageFactory extends Factory
{
    protected $model = FacebookPage::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'page_id' => $this->faker->numerify('##########'),
            'user_access_token' => $this->faker->sha256(),
            'page_access_token' => $this->faker->sha256(),
            'instagram_id' => $this->faker->optional()->numerify('##########'),
        ];
    }

    public function withInstagram(): static
    {
        return $this->state(fn (array $attributes) => [
            'instagram_id' => $this->faker->numerify('##########'),
        ]);
    }

    public function withoutInstagram(): static
    {
        return $this->state(fn (array $attributes) => [
            'instagram_id' => null,
        ]);
    }
}