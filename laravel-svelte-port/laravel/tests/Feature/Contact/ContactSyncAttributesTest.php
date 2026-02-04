<?php

namespace Tests\Feature\Contact;

use App\Models\Account;
use App\Models\Contact;
use App\Services\Contact\ContactSyncAttributesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactSyncAttributesTest extends TestCase
{
    use RefreshDatabase;

    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->account = Account::factory()->create();
    }

    /** @test */
    public function it_syncs_city_from_additional_attributes_to_location_field()
    {
        $contact = Contact::factory()->create([
            'account_id' => $this->account->id,
            'additional_attributes' => [
                'city' => 'New York',
                'company_name' => 'Test Company'
            ],
            'location' => null
        ]);

        $syncService = new ContactSyncAttributesService($contact);
        $syncService->perform();

        $this->assertEquals('New York', $contact->location);
    }

    /** @test */
    public function it_syncs_country_code_from_additional_attributes()
    {
        $contact = Contact::factory()->create([
            'account_id' => $this->account->id,
            'additional_attributes' => [
                'country_code' => 'US',
                'city' => 'Los Angeles'
            ],
            'country_code' => null
        ]);

        $syncService = new ContactSyncAttributesService($contact);
        $syncService->perform();

        $this->assertEquals('US', $contact->country_code);
        $this->assertEquals('Los Angeles', $contact->location);
    }

    /** @test */
    public function it_supports_country_key_for_backward_compatibility()
    {
        $contact = Contact::factory()->create([
            'account_id' => $this->account->id,
            'additional_attributes' => [
                'country' => 'CA', // Using 'country' instead of 'country_code'
                'city' => 'Toronto'
            ],
            'country_code' => null
        ]);

        $syncService = new ContactSyncAttributesService($contact);
        $syncService->perform();

        $this->assertEquals('CA', $contact->country_code);
    }

    /** @test */
    public function it_promotes_visitor_to_lead_when_has_email()
    {
        $contact = Contact::factory()->create([
            'account_id' => $this->account->id,
            'email' => 'test@example.com',
            'contact_type' => 0 // visitor
        ]);

        $syncService = new ContactSyncAttributesService($contact);
        $syncService->perform();

        $this->assertEquals(1, $contact->contact_type); // lead
    }

    /** @test */
    public function it_promotes_visitor_to_lead_when_has_phone()
    {
        $contact = Contact::factory()->create([
            'account_id' => $this->account->id,
            'phone_number' => '+1234567890',
            'contact_type' => 0 // visitor
        ]);

        $syncService = new ContactSyncAttributesService($contact);
        $syncService->perform();

        $this->assertEquals(1, $contact->contact_type); // lead
    }

    /** @test */
    public function it_promotes_visitor_to_lead_when_has_social_details()
    {
        $contact = Contact::factory()->create([
            'account_id' => $this->account->id,
            'additional_attributes' => [
                'social_facebook_id' => '123456789',
                'city' => 'Miami'
            ],
            'contact_type' => 0 // visitor
        ]);

        $syncService = new ContactSyncAttributesService($contact);
        $syncService->perform();

        $this->assertEquals(1, $contact->contact_type); // lead
    }

    /** @test */
    public function it_does_not_change_contact_type_if_already_lead_or_customer()
    {
        $leadContact = Contact::factory()->create([
            'account_id' => $this->account->id,
            'email' => 'lead@example.com',
            'contact_type' => 1 // lead
        ]);

        $customerContact = Contact::factory()->create([
            'account_id' => $this->account->id,
            'email' => 'customer@example.com',
            'contact_type' => 2 // customer
        ]);

        $syncService = new ContactSyncAttributesService($leadContact);
        $syncService->perform();
        $this->assertEquals(1, $leadContact->contact_type);

        $syncService = new ContactSyncAttributesService($customerContact);
        $syncService->perform();
        $this->assertEquals(2, $customerContact->contact_type);
    }

    /** @test */
    public function it_automatically_syncs_on_contact_save()
    {
        $contact = Contact::create([
            'account_id' => $this->account->id,
            'name' => 'Test Contact',
            'additional_attributes' => [
                'city' => 'Chicago',
                'country_code' => 'US'
            ]
        ]);

        // Observer should have automatically synced the attributes
        $contact->refresh();
        $this->assertEquals('Chicago', $contact->location);
        $this->assertEquals('US', $contact->country_code);
    }

    /** @test */
    public function it_handles_empty_additional_attributes_gracefully()
    {
        $contact = Contact::factory()->create([
            'account_id' => $this->account->id,
            'additional_attributes' => null
        ]);

        $syncService = new ContactSyncAttributesService($contact);
        $syncService->perform();

        // Should not throw errors
        $this->assertNull($contact->location);
        $this->assertNull($contact->country_code);
    }
}