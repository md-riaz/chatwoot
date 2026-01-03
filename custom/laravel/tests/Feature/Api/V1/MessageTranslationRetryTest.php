<?php

namespace Tests\Feature\Api\V1;

use App\Models\Account;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\Message;
use App\Models\User;
use App\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MessageTranslationRetryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Account $account;
    private Inbox $inbox;
    private Conversation $conversation;
    private Message $message;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->account = Account::factory()->create();
        $this->account->users()->attach($this->user);

        $this->inbox = Inbox::factory()->create(['account_id' => $this->account->id]);
        $this->conversation = Conversation::factory()->create([
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
        ]);
        $this->message = Message::factory()->create([
            'account_id' => $this->account->id,
            'conversation_id' => $this->conversation->id,
            'inbox_id' => $this->inbox->id,
            'content' => 'Hello world',
        ]);
    }

    public function test_translate_message_returns_cached_translation()
    {
        // Set up existing translation
        $this->message->update([
            'translations' => ['es' => 'Hola mundo']
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/messages/{$this->message->id}/translate", [
                'target_language' => 'es'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'content' => 'Hola mundo'
            ]);
    }

    public function test_translate_message_without_service_returns_null()
    {
        // Mock translation service to return null (service not available)
        $this->mock(TranslationService::class, function ($mock) {
            $mock->shouldReceive('translate')->andReturn(null);
        });

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/messages/{$this->message->id}/translate", [
                'target_language' => 'es'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'content' => null
            ]);
    }

    public function test_translate_message_with_service_saves_translation()
    {
        // Mock translation service to return translation
        $this->mock(TranslationService::class, function ($mock) {
            $mock->shouldReceive('translate')->andReturn('Hola mundo');
        });

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/messages/{$this->message->id}/translate", [
                'target_language' => 'es'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'content' => 'Hola mundo'
            ]);

        // Verify translation was saved
        $this->message->refresh();
        $this->assertEquals('Hola mundo', $this->message->translations['es']);
    }

    public function test_translate_message_validates_target_language()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/messages/{$this->message->id}/translate", [
                'target_language' => 'invalid'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['target_language']);
    }

    public function test_retry_message_updates_status_and_dispatches_job()
    {
        Queue::fake();

        // Set message as failed
        $this->message->update([
            'status' => Message::STATUS_FAILED,
            'content_attributes' => ['external_error' => 'Some error']
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/messages/{$this->message->id}/retry");

        $response->assertStatus(200);

        // Verify message status was updated
        $this->message->refresh();
        $this->assertEquals(Message::STATUS_SENT, $this->message->status);

        // Verify job was dispatched
        Queue::assertPushed(\App\Jobs\SendReplyJob::class);
    }

    public function test_retry_message_requires_authentication()
    {
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/conversations/{$this->conversation->id}/messages/{$this->message->id}/retry");

        $response->assertStatus(401);
    }

    public function test_retry_message_validates_account_ownership()
    {
        $otherAccount = Account::factory()->create();
        $otherInbox = Inbox::factory()->create(['account_id' => $otherAccount->id]);
        $otherConversation = Conversation::factory()->create([
            'account_id' => $otherAccount->id,
            'inbox_id' => $otherInbox->id,
        ]);
        $otherMessage = Message::factory()->create([
            'account_id' => $otherAccount->id,
            'conversation_id' => $otherConversation->id,
            'inbox_id' => $otherInbox->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/accounts/{$this->account->id}/conversations/{$otherConversation->id}/messages/{$otherMessage->id}/retry");

        $response->assertStatus(404);
    }
}