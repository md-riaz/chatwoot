<?php

namespace Tests\Feature\Widget;

use App\Models\Account;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\ContactInbox;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\User;
use App\Models\Channel\WebWidget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignTest extends TestCase
{
    use RefreshDatabase;

    private Account $account;
    private Inbox $inbox;
    private WebWidget $webWidget;
    private User $user;
    private Contact $contact;
    private ContactInbox $contactInbox;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()->create();
        $this->user = User::factory()->create(['account_id' => $this->account->id]);
        
        $this->inbox = Inbox::factory()->create([
            'account_id' => $this->account->id,
            'channel_type' => 'Channel::WebWidget',
        ]);

        $this->webWidget = WebWidget::factory()->create([
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
            'website_token' => 'test-token-123',
        ]);

        $this->contact = Contact::factory()->create(['account_id' => $this->account->id]);
        $this->contactInbox = ContactInbox::factory()->create([
            'contact_id' => $this->contact->id,
            'inbox_id' => $this->inbox->id,
        ]);
    }

    public function test_can_get_widget_campaigns()
    {
        // Create campaigns
        $activeCampaign = Campaign::factory()->create([
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
            'sender_id' => $this->user->id,
            'campaign_type' => Campaign::TYPE_ONGOING,
            'enabled' => true,
        ]);

        $disabledCampaign = Campaign::factory()->create([
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
            'campaign_type' => Campaign::TYPE_ONGOING,
            'enabled' => false,
        ]);

        $response = $this->getJson('/api/v1/widget/campaigns?website_token=test-token-123');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'message',
                        'sender',
                        'trigger_rules',
                        'trigger_only_during_business_hours',
                    ],
                ],
            ]);

        $data = $response->json('data');
        $this->assertCount(1, $data); // Only active campaign should be returned
        $this->assertEquals($activeCampaign->id, $data[0]['id']);
    }

    public function test_returns_empty_when_campaigns_feature_disabled()
    {
        // Disable campaigns feature
        $this->account->update(['feature_flags' => 0]); // No features enabled

        Campaign::factory()->create([
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
            'campaign_type' => Campaign::TYPE_ONGOING,
            'enabled' => true,
        ]);

        $response = $this->getJson('/api/v1/widget/campaigns?website_token=test-token-123');

        $response->assertOk()
            ->assertJson(['data' => []]);
    }

    public function test_returns_error_for_invalid_website_token()
    {
        $response = $this->getJson('/api/v1/widget/campaigns?website_token=invalid-token');

        $response->assertNotFound()
            ->assertJson(['error' => 'Invalid website token']);
    }

    public function test_can_trigger_campaign()
    {
        $campaign = Campaign::factory()->create([
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
            'sender_id' => $this->user->id,
            'campaign_type' => Campaign::TYPE_ONGOING,
            'enabled' => true,
            'message' => 'Welcome to our website!',
        ]);

        // Mock the contact session (this would normally be set by middleware)
        $this->app->instance('widget.contact_inbox', $this->contactInbox);

        $response = $this->postJson('/api/v1/widget/campaigns/trigger', [
            'website_token' => 'test-token-123',
            'campaign_id' => $campaign->id,
            'custom_attributes' => ['source' => 'campaign'],
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Campaign triggered successfully']);

        // Verify conversation was created
        $this->assertDatabaseHas('conversations', [
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
            'contact_id' => $this->contact->id,
            'campaign_id' => $campaign->id,
        ]);

        // Verify message was created
        $conversation = Conversation::where('campaign_id', $campaign->id)->first();
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'content' => 'Welcome to our website!',
            'message_type' => 'outgoing',
        ]);
    }

    public function test_cannot_trigger_disabled_campaign()
    {
        $campaign = Campaign::factory()->create([
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
            'campaign_type' => Campaign::TYPE_ONGOING,
            'enabled' => false,
        ]);

        $response = $this->postJson('/api/v1/widget/campaigns/trigger', [
            'website_token' => 'test-token-123',
            'campaign_id' => $campaign->id,
        ]);

        $response->assertNotFound()
            ->assertJson(['error' => 'Campaign not found or not active']);
    }

    public function test_cannot_trigger_campaign_when_conversation_exists()
    {
        $campaign = Campaign::factory()->create([
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
            'campaign_type' => Campaign::TYPE_ONGOING,
            'enabled' => true,
        ]);

        // Create existing conversation
        Conversation::factory()->create([
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
            'contact_id' => $this->contact->id,
            'contact_inbox_id' => $this->contactInbox->id,
        ]);

        $this->app->instance('widget.contact_inbox', $this->contactInbox);

        $response = $this->postJson('/api/v1/widget/campaigns/trigger', [
            'website_token' => 'test-token-123',
            'campaign_id' => $campaign->id,
        ]);

        $response->assertBadRequest()
            ->assertJson(['error' => 'Conversation already exists']);
    }

    public function test_campaign_includes_sender_information()
    {
        $campaign = Campaign::factory()->create([
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
            'sender_id' => $this->user->id,
            'campaign_type' => Campaign::TYPE_ONGOING,
            'enabled' => true,
        ]);

        $response = $this->getJson('/api/v1/widget/campaigns?website_token=test-token-123');

        $response->assertOk();
        
        $data = $response->json('data');
        $this->assertArrayHasKey('sender', $data[0]);
        $this->assertEquals($this->user->id, $data[0]['sender']['id']);
        $this->assertEquals($this->user->name, $data[0]['sender']['name']);
        $this->assertArrayHasKey('avatar_url', $data[0]['sender']);
    }

    public function test_campaign_includes_trigger_rules()
    {
        $triggerRules = [
            'url' => 'https://example.com/pricing',
            'timeOnPage' => 30,
        ];

        $campaign = Campaign::factory()->create([
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
            'campaign_type' => Campaign::TYPE_ONGOING,
            'enabled' => true,
            'trigger_rules' => $triggerRules,
            'trigger_only_during_business_hours' => true,
        ]);

        $response = $this->getJson('/api/v1/widget/campaigns?website_token=test-token-123');

        $response->assertOk();
        
        $data = $response->json('data');
        $this->assertEquals($triggerRules, $data[0]['trigger_rules']);
        $this->assertTrue($data[0]['trigger_only_during_business_hours']);
    }
}