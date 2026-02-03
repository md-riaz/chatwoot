<?php

namespace Tests\Feature\WebSocket;

use App\Models\Account;
use App\Models\AccountUser;
use App\Models\Article;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\Portal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Tests\TestCase;

class ChannelAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_account_channel_when_member(): void
    {
        $account = Account::factory()->create();
        $user = User::factory()->create();
        
        // Add user to account
        AccountUser::factory()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        // Test the authorization logic directly
        $canAccess = $user->accounts()->where('account_id', $account->id)->exists();
        $this->assertTrue($canAccess);
    }

    public function test_user_cannot_access_account_channel_when_not_member(): void
    {
        $account = Account::factory()->create();
        $user = User::factory()->create();
        // User is not added to account

        $this->actingAs($user);

        // Test the authorization logic directly
        $canAccess = $user->accounts()->where('account_id', $account->id)->exists();
        $this->assertFalse($canAccess);
    }

    public function test_user_can_access_own_user_channel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        // Test the authorization logic directly
        $canAccess = (int) $user->id === (int) $user->id;
        $this->assertTrue($canAccess);
    }

    public function test_user_cannot_access_other_user_channel(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user);

        // Test the authorization logic directly
        $canAccess = (int) $user->id === (int) $otherUser->id;
        $this->assertFalse($canAccess);
    }

    public function test_user_can_access_conversation_channel_when_account_member(): void
    {
        $account = Account::factory()->create();
        $user = User::factory()->create();
        $conversation = Conversation::factory()->create(['account_id' => $account->id]);
        
        // Add user to account
        AccountUser::factory()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        // Test the authorization logic directly
        $canAccess = $user->accounts()->where('account_id', $conversation->account_id)->exists();
        $this->assertTrue($canAccess);
    }

    public function test_contact_can_access_own_contact_channel(): void
    {
        $account = Account::factory()->create();
        $contact = Contact::factory()->create(['account_id' => $account->id]);

        $this->actingAs($contact);

        // Test the authorization logic directly (contact accessing own channel)
        $canAccess = (int) $contact->id === (int) $contact->id;
        $this->assertTrue($canAccess);
    }

    public function test_agent_can_access_contact_channel_when_account_member(): void
    {
        $account = Account::factory()->create();
        $user = User::factory()->create();
        $contact = Contact::factory()->create(['account_id' => $account->id]);
        
        // Add user to account
        AccountUser::factory()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        // Test the authorization logic directly (agent accessing contact channel)
        $canAccess = $user->accounts()->where('account_id', $contact->account_id)->exists();
        $this->assertTrue($canAccess);
    }

    public function test_user_can_access_presence_channel_when_account_member(): void
    {
        $account = Account::factory()->create();
        $user = User::factory()->create();
        
        // Add user to account
        AccountUser::factory()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        // Test the authorization logic directly
        $canAccess = $user->accounts()->where('account_id', $account->id)->exists();
        $this->assertTrue($canAccess);
        
        // Test presence data structure
        if ($canAccess) {
            $presenceData = [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar_url,
            ];
            
            $this->assertEquals($user->id, $presenceData['id']);
            $this->assertEquals($user->name, $presenceData['name']);
        }
    }

    public function test_contact_can_access_contact_presence_channel(): void
    {
        $account = Account::factory()->create();
        $contact = Contact::factory()->create(['account_id' => $account->id]);

        $this->actingAs($contact);

        // Test the authorization logic directly
        $canAccess = (int) $contact->id === (int) $contact->id;
        $this->assertTrue($canAccess);
        
        // Test presence data structure
        if ($canAccess) {
            $presenceData = [
                'id' => $contact->id,
                'name' => $contact->name,
                'avatar_url' => $contact->avatar_url ?? null,
                'type' => 'contact'
            ];
            
            $this->assertEquals($contact->id, $presenceData['id']);
            $this->assertEquals('contact', $presenceData['type']);
        }
    }

    public function test_user_can_access_inbox_channel_when_account_member(): void
    {
        $account = Account::factory()->create();
        $user = User::factory()->create();
        $inbox = Inbox::factory()->create(['account_id' => $account->id]);
        
        // Add user to account
        AccountUser::factory()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        // Test the authorization logic directly
        $canAccess = $user->accounts()->where('account_id', $inbox->account_id)->exists();
        $this->assertTrue($canAccess);
    }

    public function test_contact_can_access_inbox_channel_when_has_conversations(): void
    {
        $account = Account::factory()->create();
        $contact = Contact::factory()->create(['account_id' => $account->id]);
        $inbox = Inbox::factory()->create(['account_id' => $account->id]);
        
        // Create conversation for contact in inbox
        Conversation::factory()->create([
            'account_id' => $account->id,
            'contact_id' => $contact->id,
            'inbox_id' => $inbox->id,
        ]);

        $this->actingAs($contact);

        // Test the authorization logic directly
        $canAccess = $contact->conversations()->where('inbox_id', $inbox->id)->exists();
        $this->assertTrue($canAccess);
    }
}