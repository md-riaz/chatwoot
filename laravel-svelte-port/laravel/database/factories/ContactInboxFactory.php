<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\ContactInbox;
use App\Models\Inbox;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactInboxFactory extends Factory
{
    protected $model = ContactInbox::class;

    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'inbox_id' => Inbox::factory(),
            'source_id' => $this->faker->uuid(),
        ];
    }

    public function forContact(Contact $contact): static
    {
        return $this->state(fn (array $attributes) => [
            'contact_id' => $contact->id,
        ]);
    }

    public function forInbox(Inbox $inbox): static
    {
        return $this->state(fn (array $attributes) => [
            'inbox_id' => $inbox->id,
        ]);
    }
}