<?php

namespace Tests\Feature\Api\V1\Conversations;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DraftMessagesControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Account $account;
    private Inbox $inbox;
    private Contact $contact;
    private Conversation $conversation;

    protected function setUp(): void
    {
        parent::setUp();
        
        Cache::flush();
        
        $this->user = User::factory()->create();
        $this->account = Account::factory()->create();
        $this->inbox = Inbox::factory()->for($this->account)->create();
        $this->contact = Contact::factory()->for($this->account)->create();
        $this->conversation = Conversation::factory()
            ->for($this->account)
            ->for($this->inbox)
            ->for($this->contact)
            ->create();
    }

    public function test_show_returns_no_draft_when_none_exists(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/draft_messages");

        $response->assertOk()
            ->assertJson(['has_draft' => false]);
    }

    public function test_update_saves_draft_message(): void
    {
        $message = 'Test draft message';
        
        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/draft_messages", [
                'draft_message' => [
                    'message' => $message
                ]
            ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Draft saved successfully'
            ])
            ->assertJsonStructure(['updated_at']);
    }

    public function test_show_returns_draft_when_exists(): void
    {
        $message = 'Test draft message';
        
        // Save a draft first
        $this->actingAs($this->user)
            ->patchJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/draft_messages", [
                'draft_message' => [
                    'message' => $message
                ]
            ]);

        // Retrieve the draft
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/draft_messages");

        $response->assertOk()
            ->assertJson([
                'has_draft' => true,
                'message' => $message,
                'user_id' => $this->user->id
            ])
            ->assertJsonStructure(['updated_at']);
    }

    public function test_destroy_deletes_draft(): void
    {
        $message = 'Test draft message';
        
        // Save a draft first
        $this->actingAs($this->user)
            ->patchJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/draft_messages", [
                'draft_message' => [
                    'message' => $message
                ]
            ]);

        // Delete the draft
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/draft_messages");

        $response->assertNoContent();

        // Verify draft is deleted
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/draft_messages");

        $response->assertOk()
            ->assertJson(['has_draft' => false]);
    }

    public function test_drafts_are_user_specific(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $message1 = 'User 1 message';
        $message2 = 'User 2 message';

        // Save drafts for different users
        $this->actingAs($user1)
            ->patchJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/draft_messages", [
                'draft_message' => ['message' => $message1]
            ]);

        $this->actingAs($user2)
            ->patchJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/draft_messages", [
                'draft_message' => ['message' => $message2]
            ]);

        // Verify each user sees only their own draft
        $response1 = $this->actingAs($user1)
            ->getJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/draft_messages");

        $response2 = $this->actingAs($user2)
            ->getJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/draft_messages");

        $response1->assertOk()
            ->assertJson([
                'has_draft' => true,
                'message' => $message1,
                'user_id' => $user1->id
            ]);

        $response2->assertOk()
            ->assertJson([
                'has_draft' => true,
                'message' => $message2,
                'user_id' => $user2->id
            ]);
    }

    public function test_validates_conversation_belongs_to_account(): void
    {
        $otherAccount = Account::factory()->create();
        $otherInbox = Inbox::factory()->for($otherAccount)->create();
        $otherContact = Contact::factory()->for($otherAccount)->create();
        $otherConversation = Conversation::factory()
            ->for($otherAccount)
            ->for($otherInbox)
            ->for($otherContact)
            ->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/accounts/{$this->account->id}/conversations/{$otherConversation->id}/draft_messages");

        $response->assertNotFound();
    }

    public function test_requires_authentication(): void
    {
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/draft_messages");

        $response->assertUnauthorized();
    }

    public function test_validates_draft_message_data(): void
    {
        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/draft_messages", [
                'draft_message' => [
                    'message' => '' // Empty message should fail validation
                ]
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['draft_message.message']);
    }

    public function test_validates_message_length(): void
    {
        $longMessage = str_repeat('a', 10001); // Exceeds 10,000 character limit
        
        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/draft_messages", [
                'draft_message' => [
                    'message' => $longMessage
                ]
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['draft_message.message']);
    }

    public function test_conflict_resolution_works(): void
    {
        $message = 'Initial message';
        
        // Save initial draft
        $response1 = $this->actingAs($this->user)
            ->patchJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/draft_messages", [
                'draft_message' => [
                    'message' => $message
                ]
            ]);

        $initialTimestamp = $response1->json('updated_at');
        
        // Wait a moment to ensure different timestamp
        sleep(1);
        
        // Update draft (simulating another session)
        $this->actingAs($this->user)
            ->patchJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/draft_messages", [
                'draft_message' => [
                    'message' => 'Updated message'
                ]
            ]);

        // Try to save with old timestamp (should fail)
        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/draft_messages", [
                'draft_message' => [
                    'message' => 'Conflicting message',
                    'updated_at' => $initialTimestamp
                ]
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['draft_message.updated_at']);
    }

    public function test_handles_whitespace_only_message(): void
    {
        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/draft_messages", [
                'draft_message' => [
                    'message' => '   '  // Only whitespace
                ]
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['draft_message.message']);
    }

    public function test_trims_whitespace_from_message(): void
    {
        $messageWithWhitespace = '  Test message with whitespace  ';
        $expectedMessage = 'Test message with whitespace';
        
        $this->actingAs($this->user)
            ->patchJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/draft_messages", [
                'draft_message' => [
                    'message' => $messageWithWhitespace
                ]
            ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/draft_messages");

        $response->assertOk()
            ->assertJson([
                'has_draft' => true,
                'message' => $expectedMessage
            ]);
    }
}