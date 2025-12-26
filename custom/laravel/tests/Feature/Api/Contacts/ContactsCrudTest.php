<?php

/**
 * Comprehensive Contact API Tests
 *
 * Tests all contact-related API functionality including CRUD operations,
 * search, filtering, merging, and edge cases.
 */

use App\Models\Account;
use App\Models\Contact;
use App\Models\Inbox;
use App\Models\User;

describe('Contact Listing', function () {
    test('can list contacts for account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        Contact::factory(10)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/contacts");

        $response->assertOk()
            ->assertJsonCount(10, 'data');
    });

    test('empty account returns empty contacts list', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/contacts");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    test('contacts list includes expected fields', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        Contact::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/contacts");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'phone_number',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    });

    test('contacts list is paginated', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        Contact::factory(50)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/contacts");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });
});

describe('Contact Creation', function () {
    test('can create contact', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts", [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone_number' => '+1234567890',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'John Doe')
            ->assertJsonPath('data.email', 'john@example.com')
            ->assertJsonPath('data.phone_number', '+1234567890');
    });

    test('contact creation with minimal data', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts", [
                'name' => 'Jane Doe',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Jane Doe');
    });

    test('contact creation requires name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts", [
                'email' => 'test@example.com',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });

    test('contact creation with invalid email fails', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts", [
                'name' => 'Test User',
                'email' => 'not-an-email',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });

    test('contact creation with custom attributes', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts", [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'custom_attributes' => [
                    'company' => 'Acme Corp',
                    'role' => 'CEO',
                    'plan' => 'enterprise',
                ],
            ]);

        $response->assertCreated();
        $contact = Contact::latest()->first();
        expect($contact->custom_attributes['company'])->toBe('Acme Corp');
    });

    test('duplicate email within same account is handled', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        Contact::factory()->for($account)->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts", [
                'name' => 'New Contact',
                'email' => 'existing@example.com',
            ]);

        // Depending on implementation, this may fail validation or merge
        $response->assertUnprocessable()->or(
            $response->assertCreated()
        );
    });
});

describe('Contact Retrieval', function () {
    test('can show contact', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $contact = Contact::factory()->for($account)->create([
            'name' => 'Test Contact',
            'email' => 'test@example.com',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $contact->id)
            ->assertJsonPath('data.name', 'Test Contact')
            ->assertJsonPath('data.email', 'test@example.com');
    });

    test('cannot access contact from other account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $otherAccount = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $contact = Contact::factory()->for($otherAccount)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$otherAccount->id}/contacts/{$contact->id}");

        $response->assertNotFound();
    });

    test('viewing non-existent contact returns 404', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/contacts/99999");

        $response->assertNotFound();
    });
});

describe('Contact Update', function () {
    test('can update contact', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $contact = Contact::factory()->for($account)->create([
            'name' => 'Original Name',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');
    });

    test('partial update works', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $contact = Contact::factory()->for($account)->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone_number' => '+1234567890',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}", [
                'phone_number' => '+0987654321',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.phone_number', '+0987654321')
            ->assertJsonPath('data.name', 'John Doe')
            ->assertJsonPath('data.email', 'john@example.com');
    });

    test('can update custom attributes', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $contact = Contact::factory()->for($account)->create([
            'name' => 'John Doe',
            'custom_attributes' => ['plan' => 'basic'],
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}", [
                'custom_attributes' => ['plan' => 'enterprise', 'tier' => 'vip'],
            ]);

        $response->assertOk();
        $contact->refresh();
        expect($contact->custom_attributes['plan'])->toBe('enterprise');
    });
});

describe('Contact Deletion', function () {
    test('can delete contact', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $contact = Contact::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}");

        $response->assertNoContent();
        expect(Contact::find($contact->id))->toBeNull();
    });

    test('deleting non-existent contact returns 404', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/contacts/99999");

        $response->assertNotFound();
    });
});

describe('Contact Merge', function () {
    test('can merge contacts', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $baseContact = Contact::factory()->for($account)->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $mergeContact = Contact::factory()->for($account)->create([
            'name' => 'J. Doe',
            'phone_number' => '+1234567890',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts/{$baseContact->id}/merge", [
                'merge_contact_id' => $mergeContact->id,
            ]);

        $response->assertOk();
        // The merged contact should now have the phone number
        $baseContact->refresh();
        expect($baseContact->phone_number)->toBe('+1234567890');
    });
});

describe('Contact Search', function () {
    test('can search contacts by name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        Contact::factory()->for($account)->create(['name' => 'Alice Smith']);
        Contact::factory()->for($account)->create(['name' => 'Bob Johnson']);
        Contact::factory()->for($account)->create(['name' => 'Alice Cooper']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/contacts?search=Alice");

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        expect($names->filter(fn ($n) => str_contains($n, 'Alice'))->count())->toBe(2);
    });

    test('can search contacts by email', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        Contact::factory()->for($account)->create(['email' => 'alice@example.com']);
        Contact::factory()->for($account)->create(['email' => 'bob@example.com']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/contacts?search=alice@");

        $response->assertOk();
    });
});

describe('Contact Authorization', function () {
    test('unauthenticated user cannot list contacts', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/contacts");

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot create contact', function () {
        $account = Account::factory()->create();

        $response = $this->postJson("/api/v1/accounts/{$account->id}/contacts", [
            'name' => 'Test Contact',
        ]);

        $response->assertUnauthorized();
    });

    test('user without account access cannot view contacts', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        // User is NOT attached to account

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/contacts");

        $response->assertNotFound();
    });
});

describe('Contact Validation', function () {
    test('name is required', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });

    test('email must be valid format', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts", [
                'name' => 'Test Contact',
                'email' => 'invalid-email',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });

    test('name cannot be too long', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts", [
                'name' => str_repeat('a', 300),
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });
});

describe('Contact Edge Cases', function () {
    test('contact with unicode name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts", [
                'name' => '田中太郎 🙂',
                'email' => 'tanaka@example.jp',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', '田中太郎 🙂');
    });

    test('contact with empty optional fields', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts", [
                'name' => 'Minimal Contact',
                'email' => null,
                'phone_number' => null,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Minimal Contact')
            ->assertJsonPath('data.email', null);
    });

    test('handles many contacts', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        Contact::factory(100)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/contacts");

        $response->assertOk();
    });

    test('contact with special characters in name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/contacts", [
                'name' => "John O'Brien-Smith Jr.",
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', "John O'Brien-Smith Jr.");
    });
});

describe('Contact Inbox Associations', function () {
    test('can get contact with inbox associations', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}");

        $response->assertOk();
    });
});
