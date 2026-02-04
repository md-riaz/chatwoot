<?php

namespace Tests\Feature\Contact;

use App\Models\Account;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactApiTest extends TestCase
{
    use RefreshDatabase;

    private Account $account;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->account = Account::factory()->create();
        $this->user = User::factory()->create();
        $this->account->users()->attach($this->user->id, ['role' => 'administrator']);
    }

    /** @test */
    public function it_creates_contact_with_city_in_additional_attributes()
    {
        $contactData = [
            'name' => 'Lois Browning',
            'email' => 'nezija@mailinator.com',
            'phone_number' => '+880 1581 184631',
            'blocked' => false,
            'additional_attributes' => [
                'company_name' => 'Barlow and Parrish Inc',
                'city' => 'Aut velit voluptate',
                'country_code' => 'TM',
                'description' => 'Reprehenderit quis',
                'social_profiles' => []
            ],
            'custom_attributes' => []
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/accounts/{$this->account->id}/contacts", $contactData);

        $response->assertStatus(201);

        $contact = Contact::latest()->first();
        
        // Verify city is stored in additional_attributes and synced to location
        $this->assertEquals('Aut velit voluptate', $contact->additional_attributes['city']);
        $this->assertEquals('Aut velit voluptate', $contact->location); // Synced by observer
        $this->assertEquals('TM', $contact->country_code); // Synced by observer
        
        // Verify API response structure matches Rails
        $response->assertJsonStructure([
            'id',
            'name',
            'email',
            'phone_number',
            'blocked',
            'thumbnail', // Rails uses 'thumbnail' not 'avatar_url'
            'custom_attributes',
            'additional_attributes',
            'last_activity_at',
            'created_at',
            'updated_at'
        ]);

        // Verify additional_attributes contains city
        $response->assertJsonPath('additional_attributes.city', 'Aut velit voluptate');
        $response->assertJsonPath('additional_attributes.country_code', 'TM');
        $response->assertJsonPath('additional_attributes.company_name', 'Barlow and Parrish Inc');
    }

    /** @test */
    public function it_updates_contact_merging_additional_attributes()
    {
        $contact = Contact::factory()->create([
            'account_id' => $this->account->id,
            'name' => 'Original Name',
            'additional_attributes' => [
                'city' => 'Original City',
                'company_name' => 'Original Company'
            ]
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'additional_attributes' => [
                'city' => 'Updated City',
                'country_code' => 'US' // New field
                // company_name should be preserved
            ]
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/accounts/{$this->account->id}/contacts/{$contact->id}", $updateData);

        $response->assertStatus(200);

        $contact->refresh();
        
        // Verify attributes are merged, not replaced
        $this->assertEquals('Updated Name', $contact->name);
        $this->assertEquals('Updated City', $contact->additional_attributes['city']);
        $this->assertEquals('US', $contact->additional_attributes['country_code']);
        $this->assertEquals('Original Company', $contact->additional_attributes['company_name']); // Preserved
        
        // Verify sync happened
        $this->assertEquals('Updated City', $contact->location);
        $this->assertEquals('US', $contact->country_code);
    }

    /** @test */
    public function it_validates_additional_attributes_structure()
    {
        $contactData = [
            'name' => 'Test Contact',
            'additional_attributes' => [
                'city' => str_repeat('a', 300), // Too long
                'country_code' => 'INVALID_CODE', // Too long
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/accounts/{$this->account->id}/contacts", $contactData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'additional_attributes.city',
            'additional_attributes.country_code'
        ]);
    }

    /** @test */
    public function it_returns_rails_compatible_response_structure()
    {
        $contact = Contact::factory()->create([
            'account_id' => $this->account->id,
            'name' => 'Test Contact',
            'email' => 'test@example.com',
            'phone_number' => '+1234567890',
            'blocked' => false,
            'additional_attributes' => [
                'city' => 'Test City',
                'country_code' => 'US',
                'company_name' => 'Test Company'
            ],
            'custom_attributes' => [
                'department' => 'Engineering'
            ]
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/accounts/{$this->account->id}/contacts/{$contact->id}");

        $response->assertStatus(200);

        // Verify Rails-compatible structure
        $response->assertJsonStructure([
            'id',
            'name',
            'email',
            'phone_number',
            'blocked',
            'identifier',
            'thumbnail', // Rails uses 'thumbnail'
            'custom_attributes',
            'additional_attributes',
            'last_activity_at',
            'created_at',
            'updated_at'
        ]);

        // Verify timestamp format (Rails uses Unix timestamps)
        $responseData = $response->json();
        $this->assertIsInt($responseData['created_at']);
        $this->assertIsInt($responseData['updated_at']);
        
        // Verify additional_attributes structure
        $this->assertEquals('Test City', $responseData['additional_attributes']['city']);
        $this->assertEquals('US', $responseData['additional_attributes']['country_code']);
        $this->assertEquals('Test Company', $responseData['additional_attributes']['company_name']);
        
        // Verify custom_attributes structure
        $this->assertEquals('Engineering', $responseData['custom_attributes']['department']);
    }

    /** @test */
    public function it_handles_contact_type_promotion_on_creation()
    {
        $contactData = [
            'name' => 'Test Contact',
            'email' => 'test@example.com', // Should promote to lead
            'blocked' => false,
            'additional_attributes' => [
                'city' => 'Test City'
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/accounts/{$this->account->id}/contacts", $contactData);

        $response->assertStatus(201);

        $contact = Contact::latest()->first();
        $this->assertEquals(1, $contact->contact_type); // Should be promoted to lead
    }

    /** @test */
    public function it_prevents_city_duplication_in_request()
    {
        // This test ensures the old problematic payload structure is handled correctly
        $problematicPayload = [
            'name' => 'Lois Browning',
            'email' => 'nezija@mailinator.com',
            'phone_number' => '+880 1581 184631',
            'blocked' => false,
            'company' => 'Barlow and Parrish Inc',
            'city' => 'Aut velit voluptate', // This should be ignored/rejected
            'country_code' => 'TM',
            'additional_attributes' => [
                'description' => 'Reprehenderit quis',
                'company_name' => 'Barlow and Parrish Inc',
                'city' => 'Aut velit voluptate', // This is the correct place
                'country_code' => 'TM',
                'social_profiles' => []
            ],
            'custom_attributes' => []
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/accounts/{$this->account->id}/contacts", $problematicPayload);

        // Should succeed but ignore the root-level city field
        $response->assertStatus(201);

        $contact = Contact::latest()->first();
        
        // Only the city from additional_attributes should be used
        $this->assertEquals('Aut velit voluptate', $contact->additional_attributes['city']);
        $this->assertEquals('Aut velit voluptate', $contact->location);
        
        // Verify no duplication occurred
        $this->assertCount(1, array_keys($contact->additional_attributes, 'Aut velit voluptate'));
    }
}