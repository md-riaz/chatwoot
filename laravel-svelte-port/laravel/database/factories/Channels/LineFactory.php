<?php

namespace Database\Factories\Channels;

use App\Models\Account;
use App\Models\Channels\Line;
use Illuminate\Database\Eloquent\Factories\Factory;

class LineFactory extends Factory
{
    protected $model = Line::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'line_channel_id' => $this->faker->numerify('##########'),
            'line_channel_secret' => $this->faker->sha256(),
            'line_channel_token' => $this->faker->sha256(),
        ];
    }
}