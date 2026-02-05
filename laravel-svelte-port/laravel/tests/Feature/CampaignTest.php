<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Campaign;
use App\Models\Inbox;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignTest extends TestCase
{
    use RefreshDatabase;

    private Account $account;
    private Inbox $inbox;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()->create();
        $this->user = User::factory()->create(['account_id' => $this->account->id]);
        $this->inbox = Inbox::factory()->create(['account_id' => $this->account->id]);

        $this->actingAs($this->user);
    }

    public function test_can_list_campaigns()
    {
        Campaign::factory()->count(3)->create(['account_id' => $this->account->id]);

        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/campaigns");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'message',
                        'enabled',
                        'campaign_type',
                        'campaign_status',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_can_create_campaign()
    {
        $campaignData = [
            'title' => 'Welcome Campaign',
            'description' => 'Welcome new visitors',
            'message' => 'Hello! Welcome to our website.',
            'enabled' => true,
            'campaign_type' => Campaign::TYPE_ONGOING,
            'trigger_only_during_business_hours' => false,
            'inbox_id' => $this->inbox->id,
            'sender_id' => $this->user->id,
            'trigger_rules' => [
                'url' => 'https://example.com',
                'timeOnPage' => 10,
            ],
        ];

        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/campaigns", $campaignData);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'message',
                    'enabled',
                    'campaign_type',
                    'trigger_rules',
                ],
            ]);

        $this->assertDatabaseHas('campaigns', [
            'title' => 'Welcome Campaign',
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
        ]);
    }

    public function test_can_show_campaign()
    {
        $campaign = Campaign::factory()->create(['account_id' => $this->account->id]);

        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/campaigns/{$campaign->id}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $campaign->id,
                    'title' => $campaign->title,
                    'message' => $campaign->message,
                ],
            ]);
    }

    public function test_can_update_campaign()
    {
        $campaign = Campaign::factory()->create(['account_id' => $this->account->id]);

        $updateData = [
            'title' => 'Updated Campaign Title',
            'message' => 'Updated message content',
            'enabled' => false,
        ];

        $response = $this->patchJson("/api/v1/accounts/{$this->account->id}/campaigns/{$campaign->id}", $updateData);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $campaign->id,
                    'title' => 'Updated Campaign Title',
                    'message' => 'Updated message content',
                    'enabled' => false,
                ],
            ]);

        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'title' => 'Updated Campaign Title',
            'enabled' => false,
        ]);
    }

    public function test_can_delete_campaign()
    {
        $campaign = Campaign::factory()->create(['account_id' => $this->account->id]);

        $response = $this->deleteJson("/api/v1/accounts/{$this->account->id}/campaigns/{$campaign->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('campaigns', ['id' => $campaign->id]);
    }

    public function test_can_toggle_campaign_status()
    {
        $campaign = Campaign::factory()->create([
            'account_id' => $this->account->id,
            'enabled' => true,
        ]);

        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/campaigns/{$campaign->id}/toggle_status");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $campaign->id,
                    'enabled' => false,
                ],
            ]);

        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'enabled' => false,
        ]);

        // Toggle again
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/campaigns/{$campaign->id}/toggle_status");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'enabled' => true,
                ],
            ]);
    }

    public function test_cannot_access_other_account_campaigns()
    {
        $otherAccount = Account::factory()->create();
        $otherCampaign = Campaign::factory()->create(['account_id' => $otherAccount->id]);

        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/campaigns/{$otherCampaign->id}");

        $response->assertNotFound();
    }

    public function test_validates_required_fields_on_create()
    {
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/campaigns", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'message', 'inbox_id']);
    }

    public function test_validates_inbox_belongs_to_account()
    {
        $otherAccount = Account::factory()->create();
        $otherInbox = Inbox::factory()->create(['account_id' => $otherAccount->id]);

        $campaignData = [
            'title' => 'Test Campaign',
            'message' => 'Test message',
            'inbox_id' => $otherInbox->id,
        ];

        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/campaigns", $campaignData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['inbox_id']);
    }

    public function test_validates_sender_belongs_to_account()
    {
        $otherAccount = Account::factory()->create();
        $otherUser = User::factory()->create(['account_id' => $otherAccount->id]);

        $campaignData = [
            'title' => 'Test Campaign',
            'message' => 'Test message',
            'inbox_id' => $this->inbox->id,
            'sender_id' => $otherUser->id,
        ];

        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/campaigns", $campaignData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['sender_id']);
    }

    public function test_campaign_type_defaults_to_ongoing()
    {
        $campaignData = [
            'title' => 'Test Campaign',
            'message' => 'Test message',
            'inbox_id' => $this->inbox->id,
        ];

        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/campaigns", $campaignData);

        $response->assertCreated();

        $campaign = Campaign::where('title', 'Test Campaign')->first();
        $this->assertEquals(Campaign::TYPE_ONGOING, $campaign->campaign_type);
        $this->assertEquals(Campaign::STATUS_ACTIVE, $campaign->campaign_status);
    }
}