<?php

namespace Tests\Unit\WebSocket;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Services\WebSocket\BroadcastTargetService;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BroadcastTargetServiceTest extends TestCase
{
    use RefreshDatabase;

    private BroadcastTargetService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BroadcastTargetService();
    }

    public function test_get_account_channels_returns_correct_channels(): void
    {
        $accountId = 123;
        
        $channels = $this->service->getAccountChannels($accountId);
        
        $this->assertCount(2, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertInstanceOf(PresenceChannel::class, $channels[1]);
        $this->assertEquals("private-account.{$accountId}", $channels[0]->name);
        $this->assertEquals("presence-account.{$accountId}.presence", $channels[1]->name);
    }

    public function test_get_user_channels_returns_correct_channels(): void
    {
        $userId = 456;
        
        $channels = $this->service->getUserChannels($userId);
        
        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertEquals("private-user.{$userId}", $channels[0]->name);
    }

    public function test_get_conversation_channels_returns_basic_channels(): void
    {
        $account = Account::factory()->create();
        
        // Create a WebWidget channel first
        $webWidget = \App\Models\Channels\WebWidget::factory()->create();
        
        $inbox = Inbox::factory()->create([
            'account_id' => $account->id,
            'channel_id' => $webWidget->id,
        ]);
        
        $conversation = Conversation::factory()->create([
            'account_id' => $account->id,
            'contact_id' => null,
            'inbox_id' => $inbox->id,
        ]);
        
        $channels = $this->service->getConversationChannels($conversation);
        
        $this->assertCount(3, $channels); // account + conversation + inbox
        
        $channelNames = array_map(fn($channel) => $channel->name, $channels);
        $this->assertContains("private-account.{$account->id}", $channelNames);
        $this->assertContains("private-conversation.{$conversation->id}", $channelNames);
    }

    public function test_get_multi_user_channels_returns_correct_channels(): void
    {
        $userIds = [1, 2, 3];
        $contactIds = [4, 5];
        
        $channels = $this->service->getMultiUserChannels($userIds, $contactIds);
        
        $this->assertCount(5, $channels);
        
        $channelNames = array_map(fn($channel) => $channel->name, $channels);
        $this->assertContains("private-user.1", $channelNames);
        $this->assertContains("private-user.2", $channelNames);
        $this->assertContains("private-user.3", $channelNames);
        $this->assertContains("private-contact.4", $channelNames);
        $this->assertContains("private-contact.5", $channelNames);
    }

    public function test_get_presence_channels_returns_correct_channels(): void
    {
        $accountId = 789;
        
        $channels = $this->service->getPresenceChannels($accountId);
        
        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PresenceChannel::class, $channels[0]);
        $this->assertEquals("presence-account.{$accountId}.presence", $channels[0]->name);
    }

    public function test_get_channels_for_event_notification_created(): void
    {
        $userId = 123;
        
        $channels = $this->service->getChannelsForEvent('notification.created', [
            'user_id' => $userId
        ]);
        
        $this->assertCount(1, $channels);
        $this->assertEquals("private-user.{$userId}", $channels[0]->name);
    }

    public function test_get_channels_for_event_contact_created(): void
    {
        $accountId = 456;
        
        $channels = $this->service->getChannelsForEvent('contact.created', [
            'account_id' => $accountId
        ]);
        
        $this->assertCount(1, $channels);
        $this->assertEquals("private-account.{$accountId}", $channels[0]->name);
    }

    public function test_get_channels_for_event_presence_update(): void
    {
        $accountId = 789;
        
        $channels = $this->service->getChannelsForEvent('presence.update', [
            'account_id' => $accountId
        ]);
        
        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PresenceChannel::class, $channels[0]);
        $this->assertEquals("presence-account.{$accountId}.presence", $channels[0]->name);
    }

    public function test_get_channels_for_event_unknown_event(): void
    {
        $channels = $this->service->getChannelsForEvent('unknown.event', []);
        
        $this->assertCount(0, $channels);
    }
}