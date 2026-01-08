<?php

namespace Tests\Feature\Api\V1;

use App\Models\Account;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContactImportExportTest extends TestCase
{
    use RefreshDatabase;

    private Account $account;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->account = Account::factory()->create();
        $this->user = User::factory()->create(['account_id' => $this->account->id]);
        $this->actingAs($this->user);
        
        Storage::fake('local');
    }

    public function test_can_import_contacts_csv()
    {
        $csvContent = "name,email,phone_number\nJohn Doe,john@example.com,+1234567890\nJane Smith,jane@example.com,+0987654321";
        $file = UploadedFile::fake()->createWithContent('contacts.csv', $csvContent);

        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/contacts/import", [
            'import_file' => $file,
            'duplicate_handling' => 'skip'
        ]);

        $response->assertStatus(202)
                ->assertJsonStructure([
                    'message',
                    'import_id',
                    'data_import_id'
                ]);
    }

    public function test_import_requires_file()
    {
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/contacts/import");

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['import_file']);
    }

    public function test_import_validates_file_type()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/contacts/import", [
            'import_file' => $file
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['import_file']);
    }

    public function test_can_export_contacts()
    {
        // Create some test contacts
        Contact::factory()->count(3)->create(['account_id' => $this->account->id]);

        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/contacts/export", [
            'column_names' => ['id', 'name', 'email']
        ]);

        $response->assertStatus(202)
                ->assertJson([
                    'message' => 'Export queued successfully. You will be notified when it\'s ready.'
                ]);
    }

    public function test_export_validates_column_names()
    {
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/contacts/export", [
            'column_names' => ['invalid_column']
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['column_names.0']);
    }

    public function test_can_check_import_status()
    {
        $importId = 'test-import-id';

        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/contacts/imports/{$importId}/status");

        // Should return 404 for non-existent import
        $response->assertStatus(404);
    }

    public function test_can_download_export_when_available()
    {
        // This would normally be set by the export job
        \Illuminate\Support\Facades\Cache::put('export_result:' . $this->user->id, 'exports/test.csv', now()->addHour());
        Storage::put('exports/test.csv', 'id,name,email');

        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/contacts/exports/download");

        $response->assertStatus(200);
    }

    public function test_download_export_returns_404_when_not_available()
    {
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/contacts/exports/download");

        $response->assertStatus(404)
                ->assertJson(['error' => 'Export file not found or expired']);
    }
}