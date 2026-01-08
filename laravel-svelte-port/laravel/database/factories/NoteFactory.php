<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Note>
 */
class NoteFactory extends Factory
{
    protected $model = Note::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'contact_id' => Contact::factory(),
            'user_id' => User::factory(),
            'content' => fake()->paragraph(),
        ];
    }

    /**
     * Create a short note.
     */
    public function short(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => fake()->sentence(),
        ]);
    }

    /**
     * Create a long note.
     */
    public function long(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => fake()->paragraphs(3, true),
        ]);
    }
}
