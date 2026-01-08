<?php

namespace Database\Factories;

use App\Models\PlatformApp;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlatformApp>
 */
class PlatformAppFactory extends Factory
{
    protected $model = PlatformApp::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Platform',
            'access_token' => Str::random(64),
        ];
    }
}
