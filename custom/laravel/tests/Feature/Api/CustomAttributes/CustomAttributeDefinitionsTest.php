<?php

/**
 * Comprehensive Custom Attribute Definitions API Tests
 *
 * Tests all custom attribute functionality including creation,
 * validation, and usage.
 */

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\User;

describe('Custom Attribute Definition Listing', function () {
    test('can list custom attribute definitions', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions");

        $response->assertOk();
    });

    test('can filter by model type', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions?attribute_model=contact_attribute");

        $response->assertOk();
    });

    test('list includes expected fields', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });
});

describe('Custom Attribute Definition Creation', function () {
    test('can create text custom attribute', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions", [
                'attribute_display_name' => 'Company Name',
                'attribute_key' => 'company_name',
                'attribute_display_type' => 'text',
                'attribute_model' => 'contact_attribute',
            ]);

        $response->assertCreated();
    });

    test('can create number custom attribute', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions", [
                'attribute_display_name' => 'Lifetime Value',
                'attribute_key' => 'ltv',
                'attribute_display_type' => 'number',
                'attribute_model' => 'contact_attribute',
            ]);

        $response->assertCreated();
    });

    test('can create link custom attribute', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions", [
                'attribute_display_name' => 'LinkedIn Profile',
                'attribute_key' => 'linkedin_url',
                'attribute_display_type' => 'link',
                'attribute_model' => 'contact_attribute',
            ]);

        $response->assertCreated();
    });

    test('can create date custom attribute', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions", [
                'attribute_display_name' => 'Signup Date',
                'attribute_key' => 'signup_date',
                'attribute_display_type' => 'date',
                'attribute_model' => 'contact_attribute',
            ]);

        $response->assertCreated();
    });

    test('can create list custom attribute', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions", [
                'attribute_display_name' => 'Plan Type',
                'attribute_key' => 'plan_type',
                'attribute_display_type' => 'list',
                'attribute_model' => 'contact_attribute',
                'attribute_values' => ['free', 'basic', 'premium', 'enterprise'],
            ]);

        $response->assertCreated();
    });

    test('can create checkbox custom attribute', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions", [
                'attribute_display_name' => 'VIP Customer',
                'attribute_key' => 'is_vip',
                'attribute_display_type' => 'checkbox',
                'attribute_model' => 'contact_attribute',
            ]);

        $response->assertCreated();
    });

    test('can create conversation custom attribute', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions", [
                'attribute_display_name' => 'Ticket Priority',
                'attribute_key' => 'ticket_priority',
                'attribute_display_type' => 'list',
                'attribute_model' => 'conversation_attribute',
                'attribute_values' => ['low', 'medium', 'high', 'critical'],
            ]);

        $response->assertCreated();
    });

    test('attribute key must be unique per model', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        // Create first
        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions", [
                'attribute_display_name' => 'Company Name',
                'attribute_key' => 'company_name',
                'attribute_display_type' => 'text',
                'attribute_model' => 'contact_attribute',
            ])->assertCreated();

        // Try duplicate
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions", [
                'attribute_display_name' => 'Company',
                'attribute_key' => 'company_name',
                'attribute_display_type' => 'text',
                'attribute_model' => 'contact_attribute',
            ]);

        $response->assertUnprocessable();
    });

    test('requires display name', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions", [
                'attribute_key' => 'test_key',
                'attribute_display_type' => 'text',
                'attribute_model' => 'contact_attribute',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['attribute_display_name']);
    });
});

describe('Custom Attribute Definition Update', function () {
    test('can update display name', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions", [
                'attribute_display_name' => 'Original Name',
                'attribute_key' => 'test_key',
                'attribute_display_type' => 'text',
                'attribute_model' => 'contact_attribute',
            ]);

        $id = $createResponse->json('data.id');

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions/{$id}", [
                'attribute_display_name' => 'Updated Name',
            ]);

        $response->assertOk();
    });

    test('can update description', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions", [
                'attribute_display_name' => 'Test Attr',
                'attribute_key' => 'test_attr',
                'attribute_display_type' => 'text',
                'attribute_model' => 'contact_attribute',
            ]);

        $id = $createResponse->json('data.id');

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions/{$id}", [
                'attribute_description' => 'This is a helpful description',
            ]);

        $response->assertOk();
    });
});

describe('Custom Attribute Definition Deletion', function () {
    test('can delete custom attribute', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions", [
                'attribute_display_name' => 'Delete Me',
                'attribute_key' => 'delete_me',
                'attribute_display_type' => 'text',
                'attribute_model' => 'contact_attribute',
            ]);

        $id = $createResponse->json('data.id');

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions/{$id}");

        $response->assertNoContent();
    });
});

describe('Using Custom Attributes on Contacts', function () {
    test('can set custom attribute on contact', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);
        $contact = Contact::factory()->for($account)->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}", [
                'custom_attributes' => [
                    'company_name' => 'Acme Corp',
                    'ltv' => 50000,
                ],
            ]);

        $response->assertOk();
    });

    test('custom attributes are returned with contact', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);
        $contact = Contact::factory()->for($account)->create([
            'custom_attributes' => ['company' => 'Test Inc'],
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/contacts/{$contact->id}");

        $response->assertOk()
            ->assertJsonPath('data.custom_attributes.company', 'Test Inc');
    });
});

describe('Using Custom Attributes on Conversations', function () {
    test('can set custom attribute on conversation', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}", [
                'custom_attributes' => [
                    'ticket_priority' => 'high',
                    'department' => 'sales',
                ],
            ]);

        $response->assertOk();
    });
});

describe('Custom Attribute Authorization', function () {
    test('unauthenticated user cannot list custom attributes', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions");

        $response->assertUnauthorized();
    });

    test('agent cannot create custom attributes', function () {
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($agent->id, ['role' => 1]);

        $response = $this->actingAs($agent, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions", [
                'attribute_display_name' => 'Test',
                'attribute_key' => 'test',
                'attribute_display_type' => 'text',
                'attribute_model' => 'contact_attribute',
            ]);

        $response->assertForbidden();
    });
});

describe('Custom Attribute Edge Cases', function () {
    test('handles unicode attribute names', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions", [
                'attribute_display_name' => '会社名 🏢',
                'attribute_key' => 'company_jp',
                'attribute_display_type' => 'text',
                'attribute_model' => 'contact_attribute',
            ]);

        $response->assertCreated();
    });

    test('handles special characters in key', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' => 2]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/custom_attribute_definitions", [
                'attribute_display_name' => 'Test',
                'attribute_key' => 'test_key_123',
                'attribute_display_type' => 'text',
                'attribute_model' => 'contact_attribute',
            ]);

        $response->assertCreated();
    });
});
