<?php

namespace Tests\Unit\Services\Contact;

use App\Models\Account;
use App\Models\Contact;
use App\Services\Contact\ContactImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContactImportServiceTest extends TestCase
{
    use RefreshDatabase;

    private ContactImportService $service;
    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new ContactImportService();
        $this->account = Account::factory()->create();
        
        Storage::fake('local');
    }

    public function test_can_process_csv_import()
    {
        $csvContent = "name,email,phone_number\nJohn Doe,john@example.com,+1234567890\nJane Smith,jane@example.com,+0987654321";
        $filePath = 'test.csv';
        Storage::put($filePath, $csvContent);

        $result = $this->service->processImport($this->account->id, $filePath);

        $this->assertEquals('completed', $result['status']);
        $this->assertEquals(2, $result['processed']);
        $this->assertEquals(2, $result['created']);
        $this->assertEquals(0, $result['updated']);
        $this->assertEmpty($result['errors']);

        // Verify contacts were created
        $this->assertEquals(2, Contact::where('account_id', $this->account->id)->count());
    }

    public function test_handles_duplicate_contacts_with_skip()
    {
        // Create existing contact
        Contact::factory()->create([
            'account_id' => $this->account->id,
            'email' => 'john@example.com'
        ]);

        $csvContent = "name,email,phone_number\nJohn Doe,john@example.com,+1234567890\nJane Smith,jane@example.com,+0987654321";
        $filePath = 'test.csv';
        Storage::put($filePath, $csvContent);

        $result = $this->service->processImport($this->account->id, $filePath, [], 'skip');

        $this->assertEquals('completed', $result['status']);
        $this->assertEquals(2, $result['processed']);
        $this->assertEquals(1, $result['created']); // Only Jane should be created
        $this->assertEquals(0, $result['updated']);

        // Should still have 2 contacts total (1 existing + 1 new)
        $this->assertEquals(2, Contact::where('account_id', $this->account->id)->count());
    }

    public function test_handles_duplicate_contacts_with_update()
    {
        // Create existing contact
        $existingContact = Contact::factory()->create([
            'account_id' => $this->account->id,
            'email' => 'john@example.com',
            'name' => 'Old Name'
        ]);

        $csvContent = "name,email,phone_number\nJohn Doe,john@example.com,+1234567890\nJane Smith,jane@example.com,+0987654321";
        $filePath = 'test.csv';
        Storage::put($filePath, $csvContent);

        $result = $this->service->processImport($this->account->id, $filePath, [], 'update');

        $this->assertEquals('completed', $result['status']);
        $this->assertEquals(2, $result['processed']);
        $this->assertEquals(1, $result['created']); // Jane
        $this->assertEquals(1, $result['updated']); // John

        // Verify existing contact was updated
        $existingContact->refresh();
        $this->assertEquals('John Doe', $existingContact->name);
    }

    public function test_validates_email_format()
    {
        $csvContent = "name,email,phone_number\nJohn Doe,invalid-email,+1234567890\nJane Smith,jane@example.com,+0987654321";
        $filePath = 'test.csv';
        Storage::put($filePath, $csvContent);

        $result = $this->service->processImport($this->account->id, $filePath);

        $this->assertEquals('completed', $result['status']);
        $this->assertEquals(2, $result['processed']);
        $this->assertEquals(1, $result['created']); // Only Jane
        $this->assertEquals(0, $result['updated']);
        $this->assertCount(1, $result['errors']); // John's invalid email
        $this->assertCount(1, $result['failed_records']);
    }

    public function test_requires_at_least_one_identifier()
    {
        $csvContent = "name\nJohn Doe\nJane Smith";
        $filePath = 'test.csv';
        Storage::put($filePath, $csvContent);

        $result = $this->service->processImport($this->account->id, $filePath);

        $this->assertEquals('completed', $result['status']);
        $this->assertEquals(2, $result['processed']);
        $this->assertEquals(0, $result['created']);
        $this->assertEquals(0, $result['updated']);
        $this->assertCount(2, $result['errors']); // Both rows missing identifiers
    }

    public function test_formats_phone_numbers()
    {
        $csvContent = "name,email,phone_number\nJohn Doe,john@example.com,1234567890";
        $filePath = 'test.csv';
        Storage::put($filePath, $csvContent);

        $result = $this->service->processImport($this->account->id, $filePath);

        $this->assertEquals('completed', $result['status']);
        $this->assertEquals(1, $result['created']);

        $contact = Contact::where('account_id', $this->account->id)->first();
        $this->assertEquals('+1234567890', $contact->phone_number);
    }

    public function test_handles_custom_mapping()
    {
        $csvContent = "full_name,email_address,mobile\nJohn Doe,john@example.com,+1234567890";
        $filePath = 'test.csv';
        Storage::put($filePath, $csvContent);

        $mapping = [
            'full_name' => 'name',
            'email_address' => 'email',
            'mobile' => 'phone_number'
        ];

        $result = $this->service->processImport($this->account->id, $filePath, $mapping);

        $this->assertEquals('completed', $result['status']);
        $this->assertEquals(1, $result['created']);

        $contact = Contact::where('account_id', $this->account->id)->first();
        $this->assertEquals('John Doe', $contact->name);
        $this->assertEquals('john@example.com', $contact->email);
        $this->assertEquals('+1234567890', $contact->phone_number);
    }

    public function test_handles_additional_and_custom_attributes()
    {
        $csvContent = "name,email,company,city,custom_field\nJohn Doe,john@example.com,Acme Corp,New York,Custom Value";
        $filePath = 'test.csv';
        Storage::put($filePath, $csvContent);

        $result = $this->service->processImport($this->account->id, $filePath);

        $this->assertEquals('completed', $result['status']);
        $this->assertEquals(1, $result['created']);

        $contact = Contact::where('account_id', $this->account->id)->first();
        $this->assertEquals('Acme Corp', $contact->additional_attributes['company']);
        $this->assertEquals('New York', $contact->additional_attributes['city']);
        $this->assertEquals('Custom Value', $contact->custom_attributes['custom_field']);
    }
}