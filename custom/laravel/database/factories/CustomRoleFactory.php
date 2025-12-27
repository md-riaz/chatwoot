<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\CustomRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomRole>
 */
class CustomRoleFactory extends Factory
{
    protected $model = CustomRole::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'name' => fake()->unique()->jobTitle(),
            'description' => fake()->optional()->sentence(),
            'permissions' => ['conversation.view', 'conversation.assign'],
        ];
    }
}
