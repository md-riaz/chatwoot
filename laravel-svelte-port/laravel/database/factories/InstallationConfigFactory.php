<?php

namespace Database\Factories;

use App\Models\InstallationConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InstallationConfig>
 */
class InstallationConfigFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = InstallationConfig::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $types = array_keys(InstallationConfig::TYPES);
        $type = $this->faker->randomElement($types);

        return [
            'name' => strtoupper($this->faker->unique()->words(2, true)),
            'serialized_value' => ['value' => $this->generateValueForType($type)],
            'display_title' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'type' => $type,
            'locked' => $this->faker->boolean(20), // 20% chance of being locked
            'options' => $type === 'select' ? $this->faker->words(3) : null,
        ];
    }

    /**
     * Generate a value appropriate for the given type.
     */
    private function generateValueForType(string $type)
    {
        return match ($type) {
            'boolean' => $this->faker->boolean(),
            'integer' => $this->faker->numberBetween(1, 1000),
            'float' => $this->faker->randomFloat(2, 0, 1000),
            'array' => $this->faker->words(3),
            'select' => $this->faker->word(),
            'secret' => $this->faker->password(),
            'code' => $this->faker->text(100),
            default => $this->faker->sentence(),
        };
    }

    /**
     * Create a boolean configuration.
     */
    public function boolean(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'boolean',
            'serialized_value' => ['value' => $this->faker->boolean()],
            'options' => null,
        ]);
    }

    /**
     * Create an integer configuration.
     */
    public function integer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'integer',
            'serialized_value' => ['value' => $this->faker->numberBetween(1, 1000)],
            'options' => null,
        ]);
    }

    /**
     * Create a float configuration.
     */
    public function float(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'float',
            'serialized_value' => ['value' => $this->faker->randomFloat(2, 0, 1000)],
            'options' => null,
        ]);
    }

    /**
     * Create an array configuration.
     */
    public function array(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'array',
            'serialized_value' => ['value' => $this->faker->words(3)],
            'options' => null,
        ]);
    }

    /**
     * Create a select configuration.
     */
    public function select(): static
    {
        $options = $this->faker->words(3);
        
        return $this->state(fn (array $attributes) => [
            'type' => 'select',
            'serialized_value' => ['value' => $this->faker->randomElement($options)],
            'options' => $options,
        ]);
    }

    /**
     * Create a secret configuration.
     */
    public function secret(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'secret',
            'serialized_value' => ['value' => $this->faker->password()],
            'options' => null,
        ]);
    }

    /**
     * Create a code configuration.
     */
    public function code(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'code',
            'serialized_value' => ['value' => $this->faker->text(100)],
            'options' => null,
        ]);
    }

    /**
     * Create a text configuration.
     */
    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'text',
            'serialized_value' => ['value' => $this->faker->sentence()],
            'options' => null,
        ]);
    }

    /**
     * Create a locked configuration.
     */
    public function locked(): static
    {
        return $this->state(fn (array $attributes) => [
            'locked' => true,
        ]);
    }

    /**
     * Create an unlocked configuration.
     */
    public function unlocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'locked' => false,
        ]);
    }

    /**
     * Create a configuration with specific name.
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }

    /**
     * Create a configuration with specific value.
     */
    public function withValue($value): static
    {
        return $this->state(fn (array $attributes) => [
            'serialized_value' => ['value' => $value],
        ]);
    }

    /**
     * Create a configuration for Facebook integration.
     */
    public function facebook(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'FB_' . strtoupper($this->faker->word()),
            'display_title' => 'Facebook ' . $this->faker->words(2, true),
            'description' => 'Facebook integration configuration',
            'type' => 'text',
        ]);
    }

    /**
     * Create a configuration for Shopify integration.
     */
    public function shopify(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'SHOPIFY_' . strtoupper($this->faker->word()),
            'display_title' => 'Shopify ' . $this->faker->words(2, true),
            'description' => 'Shopify integration configuration',
            'type' => 'text',
        ]);
    }

    /**
     * Create a general system configuration.
     */
    public function general(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => strtoupper($this->faker->words(2, true)),
            'display_title' => $this->faker->words(3, true),
            'description' => 'General system configuration',
        ]);
    }
}