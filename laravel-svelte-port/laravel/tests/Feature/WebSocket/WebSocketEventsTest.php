<?php

namespace Tests\Feature\WebSocket;

use App\Events\Account\AccountCacheInvalidated;
use App\Events\Contact\ContactDeleted;
use App\Events\Contact\ContactMerged;
use App\Events\Conversation\AssigneeChanged;
use App\Events\Conversation\ConversationContactChanged;
use App\Events\Conversation\ConversationMentioned;
use App\Events\Conversation\ConversationRead;
use App\Events\Conversation\ConversationTyping;
use App\Events\Conversation\FirstReplyCreated;
use App\Events\Conversation\TeamChanged;
use App\Events\Notification\NotificationDeleted;
use App\Events\Notification\NotificationUpdated;
use App\Events\Presence\PresenceUpdate;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Team;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class WebSocketEventsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
    }

    public function test_notification_updated_broadcasts_to_user_channel(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create(['user_id' => $user->id]);
        
        $event = new NotificationUpdated($notification, $user);
        
        $channels = $event->broadcastOn();
        
        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertEquals("private-user.{$user->id}", $channels[0]->name);
        $this->assertEquals('notification.updated', $event->broadcastAs());
    }

    public function test_notification_deleted_broadcasts_to_user_channel(): void
    {
        $user = User::factory()->create();
        
        $event = new NotificationDeleted(123, $user->id, ['title' => 'Test'], $user);
        
        $channels = $event->broadcastOn();
        
        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertEquals("private-user.{$user->id}", $channels[0]->name);
        $this->assertEquals('notification.deleted', $event->broadcastAs());
    }

    public function test_conversation_read_broadcasts_to_conversation_channels(): void
    {
        $account = Account::factory()->create();
        $conversation = Conversation::factory()->create(['account_id' => $account->id]);
        $user = User::factory()->create();
        
        $event = new ConversationRead($conversation, $user);
        
        $channels = $event->broadcastOn();
        
        $this->assertGreaterThanOrEqual(2, count($channels));
        $this->assertEquals('conversation.read', $event->broadcastAs());
        
        $channelNames = array_map(fn($channel) => $channel->name, $channels);
        $this->assertContains("private-account.{$account->id}", $channelNames);
        $this->assertContains("private-conversation.{$conversation->id}", $channelNames);
    }

    public function test_conversation_typing_excludes_typer_from_broadcast(): void
    {
        $account = Account::factory()->create();
        $conversation = Conversation::factory()->create(['account_id' => $account->id]);
        $user = User::factory()->create();
        
        $event = new ConversationTyping($conversation, $user, true);
        
        $this->assertTrue($event->broadcastToOthers());
        $this->assertEquals('conversation.typing_on', $event->broadcastAs());
        
        $event = new ConversationTyping($conversation, $user, false);
        $this->assertEquals('conversation.typing_off', $event->broadcastAs());
    }

    public function test_assignee_changed_includes_correct_data(): void
    {
        $account = Account::factory()->create();
        $conversation = Conversation::factory()->create(['account_id' => $account->id]);
        $previousAssignee = User::factory()->create();
        $newAssignee = User::factory()->create();
        $performer = User::factory()->create();
        
        $event = new AssigneeChanged($conversation, $previousAssignee, $newAssignee, $performer);
        
        $data = $event->broadcastWith();
        
        $this->assertEquals('assignee.changed', $event->broadcastAs());
        $this->assertArrayHasKey('conversation', $data);
        $this->assertArrayHasKey('previous_assignee', $data);
        $this->assertArrayHasKey('new_assignee', $data);
        $this->assertArrayHasKey('performer', $data);
        $this->assertArrayHasKey('timestamp', $data);
        
        $this->assertEquals($previousAssignee->id, $data['previous_assignee']['id']);
        $this->assertEquals($newAssignee->id, $data['new_assignee']['id']);
        $this->assertEquals($performer->id, $data['performer']['id']);
    }

    public function test_team_changed_includes_correct_data(): void
    {
        $account = Account::factory()->create();
        $conversation = Conversation::factory()->create(['account_id' => $account->id]);
        $previousTeam = Team::factory()->create(['account_id' => $account->id]);
        $newTeam = Team::factory()->create(['account_id' => $account->id]);
        $performer = User::factory()->create();
        
        $event = new TeamChanged($conversation, $previousTeam, $newTeam, $performer);
        
        $data = $event->broadcastWith();
        
        $this->assertEquals('team.changed', $event->broadcastAs());
        $this->assertArrayHasKey('previous_team', $data);
        $this->assertArrayHasKey('new_team', $data);
        $this->assertEquals($previousTeam->id, $data['previous_team']['id']);
        $this->assertEquals($newTeam->id, $data['new_team']['id']);
    }

    public function test_conversation_mentioned_broadcasts_to_mentioned_user(): void
    {
        $account = Account::factory()->create();
        $conversation = Conversation::factory()->create(['account_id' => $account->id]);
        $mentionedUser = User::factory()->create();
        $mentioner = User::factory()->create();
        $message = Message::factory()->create(['conversation_id' => $conversation->id]);
        
        $event = new ConversationMentioned($conversation, $mentionedUser, $message, $mentioner);
        
        $channels = $event->broadcastOn();
        
        $this->assertCount(1, $channels);
        $this->assertEquals("private-user.{$mentionedUser->id}", $channels[0]->name);
        $this->assertEquals('conversation.mentioned', $event->broadcastAs());
    }

    public function test_first_reply_created_broadcasts_to_account(): void
    {
        $account = Account::factory()->create();
        $conversation = Conversation::factory()->create(['account_id' => $account->id]);
        $message = Message::factory()->create(['conversation_id' => $conversation->id]);
        $agent = User::factory()->create();
        
        $event = new FirstReplyCreated($conversation, $message, $agent);
        
        $channels = $event->broadcastOn();
        
        $this->assertCount(1, $channels);
        $this->assertEquals("private-account.{$account->id}", $channels[0]->name);
        $this->assertEquals('first.reply.created', $event->broadcastAs());
    }

    public function test_contact_merged_broadcasts_to_account(): void
    {
        $account = Account::factory()->create();
        $primaryContact = Contact::factory()->create(['account_id' => $account->id]);
        $mergedContact = Contact::factory()->create(['account_id' => $account->id]);
        $performer = User::factory()->create();
        
        $event = new ContactMerged($primaryContact, $mergedContact, $performer);
        
        $channels = $event->broadcastOn();
        
        $this->assertCount(1, $channels);
        $this->assertEquals("private-account.{$account->id}", $channels[0]->name);
        $this->assertEquals('contact.merged', $event->broadcastAs());
    }

    public function test_contact_deleted_broadcasts_to_account(): void
    {
        $account = Account::factory()->create();
        $performer = User::factory()->create();
        
        $event = new ContactDeleted(123, $account->id, ['name' => 'Test Contact'], $performer);
        
        $channels = $event->broadcastOn();
        
        $this->assertCount(1, $channels);
        $this->assertEquals("private-account.{$account->id}", $channels[0]->name);
        $this->assertEquals('contact.deleted', $event->broadcastAs());
    }

    public function test_presence_update_includes_correct_user_data(): void
    {
        $account = Account::factory()->create();
        $user = User::factory()->create();
        
        $event = new PresenceUpdate($user, $account->id, 'online', ['last_seen' => now()]);
        
        $data = $event->broadcastWith();
        
        $this->assertEquals('presence.update', $event->broadcastAs());
        $this->assertEquals($user->id, $data['user']['id']);
        $this->assertEquals('online', $data['status']);
        $this->assertEquals('agent', $data['user']['type']);
        $this->assertArrayHasKey('metadata', $data);
        $this->assertArrayHasKey('timestamp', $data);
    }

    public function test_presence_update_handles_contact_user(): void
    {
        $account = Account::factory()->create();
        $contact = Contact::factory()->create(['account_id' => $account->id]);
        
        $event = new PresenceUpdate($contact, $account->id, 'online');
        
        $data = $event->broadcastWith();
        
        $this->assertEquals($contact->id, $data['user']['id']);
        $this->assertEquals('contact', $data['user']['type']);
        $this->assertNull($data['user']['availability']); // Contacts don't have availability
    }

    public function test_account_cache_invalidated_broadcasts_to_account(): void
    {
        $account = Account::factory()->create();
        
        $event = new AccountCacheInvalidated($account, ['conversations', 'contacts']);
        
        $channels = $event->broadcastOn();
        $data = $event->broadcastWith();
        
        $this->assertCount(1, $channels);
        $this->assertEquals("private-account.{$account->id}", $channels[0]->name);
        $this->assertEquals('account.cache_invalidated', $event->broadcastAs());
        $this->assertEquals(['conversations', 'contacts'], $data['invalidated_keys']);
    }
}