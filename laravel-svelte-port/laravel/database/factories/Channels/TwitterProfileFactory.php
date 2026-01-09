<?php

namespace Database\Factories\Channels;

use App\Models\Account;
use App\Models\Channels\TwitterProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class TwitterProfileFactory extends Factory
{
    protected $model = TwitterProfile::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'profile_id' => $this->faker->numerify('##########'),
            'twitter_access_token' => $this->faker->sha256(),
            'twitter_access_token_secret' => $this->faker->sha256(),
            'tweets_enabled' => $this->faker->boolean(),
        ];
    }

    public function withTweets(): static
    {
        return $this->state(fn (array $attributes) => [
            'tweets_enabled' => true,
        ]);
    }

    public function withoutTweets(): static
    {
        return $this->state(fn (array $attributes) => [
            'tweets_enabled' => false,
        ]);
    }
}