<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Account;
use App\Models\Inbox;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Team;
use App\Models\Label;
use App\Models\AutomationRule;
use App\Models\CannedResponse;
use App\Models\Webhook;
use App\Models\Campaign;
use App\Models\SlaPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

/**
 * Feature Matrix Validation Test Suite
 * 
 * Comprehensive validation of all features against Rails functionality.
 * This test creates a complete feature matrix and validates each feature
 * with actual functionality testing.
 * 
 * Reference: TASK_21_FINAL_CHECKPOINT_VALIDATION_REPORT.md
 * Task: 29.2 Functional Parity Validation - Feature Matrix Testing
 */
class FeatureMatrixValidationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private Account $account;
    private Inbox $inbox;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->account = Account::factory()->create();
        $this->account->users()->attach($this->user, ['role' => 'administrator']);
        $this->inbox = Inbox::factory()->create(['account_id' => $this->account->id]);
        
        Sanctum::actingAs($this->user);
    }

    /**
     * Feature Matrix: Core API Endpoints (100% Parity Expected)
     * 
     * Validates all core CRUD operations match Rails functionality
     */
    public function test_core_api_endpoints_feature_matrix(): void
    {
        $this->validate_accounts_feature_set();
        $this->validate_conversations_feature_set();
        $this->validate_messages_feature_set();
        $this->validate_contacts_feature_set();
        $this->validate_inboxes_feature_set();
        $this->validate_teams_feature_set();
        $this->validate_labels_feature_set();
    }

    /**
     * Feature Matrix: Channel Integrations (95% Parity Expected)
     * 
     * Validates all channel types and their specific features
     */
    public function test_channel_integrations_feature_matrix(): void
    {
        $this->validate_whatsapp_channel_features();
        $this->validate_facebook_channel_features();
        $this->validate_email_channel_features();
        $this->validate_sms_channel_features();
        $this->validate_voice_channel_features();
        $this->validate_telegram_channel_features();
        $this->validate_twitter_channel_features();
        $this->validate_web_widget_features();
    }

    /**
     * Feature Matrix: Advanced Features (98% Parity Expected)
     * 
     * Validates automation, reporting, and advanced functionality
     */
    public function test_advanced_features_matrix(): void
    {
        $this->validate_automation_rules_features();
        $this->validate_canned_responses_features();
        $this->validate_webhooks_features();
        $this->validate_campaigns_features();
        $this->validate_reports_features();
        $this->validate_search_features();
        $this->validate_bulk_actions_features();
    }

    /**
     * Feature Matrix: Enterprise Features (75% Parity Expected)
     * 
     * Validates enterprise-specific functionality
     */
    public function test_enterprise_features_matrix(): void
    {
        $this->validate_sla_policies_features();
        $this->validate_custom_roles_features();
        $this->validate_saml_sso_features();
        $this->validate_audit_logs_features();
    }

    /**
     * Feature Matrix: Widget and Public APIs (100% Parity Expected)
     * 
     * Validates customer-facing APIs
     */
    public function test_widget_public_apis_matrix(): void
    {
        $this->validate_widget_api_features();
        $this->validate_public_api_features();
        $this->validate_platform_api_features();
    }

    // ========================================
    // Core API Endpoints Validation
    // ========================================

    private function validate_accounts_feature_set(): void
    {
        // Feature: Account CRUD Operations
        $response = $this->getJson('/api/v1/accounts');
        $response->assertStatus(200)->assertJsonStructure(['data']);
        
        $accountData = [
            'name' => 'Test Account',
            'locale' => 'en',
            'timezone' => 'UTC'
        ];
        
        $response = $this->postJson('/api/v1/accounts', $accountData);
        $response->assertStatus(201)->assertJsonFragment(['name' => 'Test Account']);
        
        $accountId = $response->json('data.id');
        
        $response = $this->patchJson("/api/v1/accounts/{$accountId}", ['name' => 'Updated Account']);
        $response->assertStatus(200)->assertJsonFragment(['name' => 'Updated Account']);
        
        $response = $this->getJson("/api/v1/accounts/{$accountId}");
        $response->assertStatus(200)->assertJsonFragment(['name' => 'Updated Account']);
        
        // Feature: Account Settings Management
        $response = $this->patchJson("/api/v1/accounts/{$accountId}", [
            'timezone' => 'America/New_York',
            'locale' => 'es'
        ]);
        $response->assertStatus(200)
                ->assertJsonFragment(['timezone' => 'America/New_York'])
                ->assertJsonFragment(['locale' => 'es']);
    }

    private function validate_conversations_feature_set(): void
    {
        $contact = Contact::factory()->create(['account_id' => $this->account->id]);
        
        // Feature: Conversation CRUD Operations
        $conversationData = [
            'contact_id' => $contact->id,
            'inbox_id' => $this->inbox->id,
            'status' => 'open'
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/conversations", $conversationData);
        $response->assertStatus(201)->assertJsonFragment(['status' => 'open']);
        
        $conversationId = $response->json('data.id');
        
        // Feature: Conversation Status Management
        $response = $this->patchJson("/api/v1/accounts/{$this->account->id}/conversations/{$conversationId}", [
            'status' => 'resolved'
        ]);
        $response->assertStatus(200)->assertJsonFragment(['status' => 'resolved']);
        
        // Feature: Conversation Assignment
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/conversations/{$conversationId}/assign", [
            'assignee_id' => $this->user->id
        ]);
        $response->assertStatus(200)->assertJsonFragment(['assignee_id' => $this->user->id]);
        
        // Feature: Conversation Filtering
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/conversations?status=resolved");
        $response->assertStatus(200);
        
        $conversations = $response->json('data');
        foreach ($conversations as $conversation) {
            $this->assertEquals('resolved', $conversation['status']);
        }
        
        // Feature: Conversation Bulk Actions
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/conversations/bulk_actions", [
            'type' => 'update_status',
            'ids' => [$conversationId],
            'status' => 'closed'
        ]);
        $response->assertStatus(200);
    }

    private function validate_messages_feature_set(): void
    {
        $contact = Contact::factory()->create(['account_id' => $this->account->id]);
        $conversation = Conversation::factory()->create([
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
            'contact_id' => $contact->id
        ]);
        
        // Feature: Message CRUD Operations
        $messageData = [
            'content' => 'Test message content',
            'message_type' => 'outgoing',
            'sender_type' => 'User',
            'sender_id' => $this->user->id
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/conversations/{$conversation->id}/messages", $messageData);
        $response->assertStatus(201)->assertJsonFragment(['content' => 'Test message content']);
        
        $messageId = $response->json('data.id');
        
        // Feature: Message Updates
        $response = $this->patchJson("/api/v1/accounts/{$this->account->id}/conversations/{$conversation->id}/messages/{$messageId}", [
            'content' => 'Updated message content'
        ]);
        $response->assertStatus(200)->assertJsonFragment(['content' => 'Updated message content']);
        
        // Feature: Message Types Support
        $messageTypes = ['incoming', 'outgoing', 'activity', 'template'];
        
        foreach ($messageTypes as $type) {
            $response = $this->postJson("/api/v1/accounts/{$this->account->id}/conversations/{$conversation->id}/messages", [
                'content' => "Test {$type} message",
                'message_type' => $type,
                'sender_type' => 'User',
                'sender_id' => $this->user->id
            ]);
            $response->assertStatus(201)->assertJsonFragment(['message_type' => $type]);
        }
        
        // Feature: Message Attachments
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/conversations/{$conversation->id}/messages", [
            'content' => 'Message with attachment',
            'message_type' => 'outgoing',
            'sender_type' => 'User',
            'sender_id' => $this->user->id,
            'attachments' => [
                [
                    'file_type' => 'image',
                    'file_url' => 'https://example.com/image.jpg'
                ]
            ]
        ]);
        $response->assertStatus(201);
    }

    private function validate_contacts_feature_set(): void
    {
        // Feature: Contact CRUD Operations
        $contactData = [
            'name' => 'Test Contact',
            'email' => 'test@example.com',
            'phone_number' => '+1234567890'
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/contacts", $contactData);
        $response->assertStatus(201)->assertJsonFragment(['name' => 'Test Contact']);
        
        $contactId = $response->json('data.id');
        
        // Feature: Contact Updates
        $response = $this->patchJson("/api/v1/accounts/{$this->account->id}/contacts/{$contactId}", [
            'name' => 'Updated Contact'
        ]);
        $response->assertStatus(200)->assertJsonFragment(['name' => 'Updated Contact']);
        
        // Feature: Contact Custom Attributes
        $response = $this->patchJson("/api/v1/accounts/{$this->account->id}/contacts/{$contactId}", [
            'custom_attributes' => [
                'company' => 'Test Company',
                'department' => 'Engineering'
            ]
        ]);
        $response->assertStatus(200)
                ->assertJsonPath('data.custom_attributes.company', 'Test Company');
        
        // Feature: Contact Search
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/contacts?q=Updated");
        $response->assertStatus(200);
        
        $contacts = $response->json('data');
        $this->assertNotEmpty($contacts);
        $this->assertStringContainsString('Updated', $contacts[0]['name']);
        
        // Feature: Contact Merge
        $contact2 = Contact::factory()->create(['account_id' => $this->account->id]);
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/contacts/{$contactId}/merge", [
            'child_contact_id' => $contact2->id
        ]);
        $response->assertStatus(200);
    }

    private function validate_inboxes_feature_set(): void
    {
        // Feature: Inbox CRUD Operations
        $inboxData = [
            'name' => 'Test Inbox',
            'channel_type' => 'email',
            'email' => 'test@example.com'
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/inboxes", $inboxData);
        $response->assertStatus(201)->assertJsonFragment(['name' => 'Test Inbox']);
        
        $inboxId = $response->json('data.id');
        
        // Feature: Inbox Member Management
        $agent = User::factory()->create();
        $this->account->users()->attach($agent, ['role' => 'agent']);
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/inboxes/{$inboxId}/members", [
            'user_ids' => [$agent->id]
        ]);
        $response->assertStatus(200);
        
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/inboxes/{$inboxId}/members");
        $response->assertStatus(200);
        
        $members = $response->json('data');
        $memberIds = collect($members)->pluck('id')->toArray();
        $this->assertContains($agent->id, $memberIds);
        
        // Feature: Inbox Settings
        $response = $this->patchJson("/api/v1/accounts/{$this->account->id}/inboxes/{$inboxId}", [
            'enable_auto_assignment' => true,
            'greeting_enabled' => true,
            'greeting_message' => 'Welcome to our support!'
        ]);
        $response->assertStatus(200)
                ->assertJsonFragment(['enable_auto_assignment' => true])
                ->assertJsonFragment(['greeting_message' => 'Welcome to our support!']);
    }

    private function validate_teams_feature_set(): void
    {
        // Feature: Team CRUD Operations
        $teamData = [
            'name' => 'Test Team',
            'description' => 'Test team description'
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/teams", $teamData);
        $response->assertStatus(201)->assertJsonFragment(['name' => 'Test Team']);
        
        $teamId = $response->json('data.id');
        
        // Feature: Team Member Management
        $agent = User::factory()->create();
        $this->account->users()->attach($agent, ['role' => 'agent']);
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/teams/{$teamId}/members", [
            'user_ids' => [$agent->id]
        ]);
        $response->assertStatus(200);
        
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/teams/{$teamId}/members");
        $response->assertStatus(200);
        
        $members = $response->json('data');
        $memberIds = collect($members)->pluck('id')->toArray();
        $this->assertContains($agent->id, $memberIds);
    }

    private function validate_labels_feature_set(): void
    {
        // Feature: Label CRUD Operations
        $labelData = [
            'title' => 'Test Label',
            'description' => 'Test label description',
            'color' => '#FF0000'
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/labels", $labelData);
        $response->assertStatus(201)->assertJsonFragment(['title' => 'Test Label']);
        
        $labelId = $response->json('data.id');
        
        // Feature: Label Assignment to Conversations
        $contact = Contact::factory()->create(['account_id' => $this->account->id]);
        $conversation = Conversation::factory()->create([
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
            'contact_id' => $contact->id
        ]);
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/conversations/{$conversation->id}/labels", [
            'labels' => [$labelId]
        ]);
        $response->assertStatus(200);
        
        // Verify label assignment
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/conversations/{$conversation->id}");
        $response->assertStatus(200);
        
        $labels = $response->json('data.labels');
        $labelIds = collect($labels)->pluck('id')->toArray();
        $this->assertContains($labelId, $labelIds);
    }

    // ========================================
    // Channel Integrations Validation
    // ========================================

    private function validate_whatsapp_channel_features(): void
    {
        // Feature: WhatsApp Channel Creation
        $channelData = [
            'name' => 'WhatsApp Test',
            'provider' => 'whatsapp_cloud',
            'provider_config' => [
                'phone_number_id' => '123456789',
                'business_account_id' => '987654321',
                'webhook_verify_token' => 'test_token'
            ]
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/channels/whatsapp", $channelData);
        $response->assertStatus(201)->assertJsonFragment(['name' => 'WhatsApp Test']);
        
        // Feature: WhatsApp Webhook Verification
        $response = $this->getJson('/api/v1/webhooks/whatsapp?hub.mode=subscribe&hub.challenge=test_challenge&hub.verify_token=test_token');
        $response->assertStatus(200)->assertSee('test_challenge');
        
        // Feature: WhatsApp Template Support
        $inboxId = $response->json('data.id');
        
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/inboxes/{$inboxId}/whatsapp/templates");
        $response->assertStatus(200);
    }

    private function validate_facebook_channel_features(): void
    {
        // Feature: Facebook Channel Creation
        $channelData = [
            'name' => 'Facebook Test',
            'page_id' => '123456789',
            'page_access_token' => 'test_token'
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/channels/facebook", $channelData);
        $response->assertStatus(201)->assertJsonFragment(['name' => 'Facebook Test']);
        
        // Feature: Facebook Pages Listing
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/channels/facebook/pages");
        $response->assertStatus(200);
    }

    private function validate_email_channel_features(): void
    {
        // Feature: Email Channel Creation
        $channelData = [
            'name' => 'Email Test',
            'email' => 'test@example.com',
            'imap_enabled' => true,
            'imap_host' => 'imap.example.com',
            'imap_port' => 993,
            'imap_login' => 'test@example.com',
            'imap_password' => 'password'
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/channels/email", $channelData);
        $response->assertStatus(201)->assertJsonFragment(['name' => 'Email Test']);
        
        // Feature: Email Configuration Test
        $inboxId = $response->json('data.id');
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/inboxes/{$inboxId}/email/test", [
            'email' => 'test@example.com'
        ]);
        $response->assertStatus(200);
    }

    private function validate_sms_channel_features(): void
    {
        // Feature: SMS Channel Creation
        $channelData = [
            'name' => 'SMS Test',
            'phone_number' => '+1234567890',
            'provider' => 'twilio',
            'provider_config' => [
                'account_sid' => 'test_sid',
                'auth_token' => 'test_token'
            ]
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/channels/sms", $channelData);
        $response->assertStatus(201)->assertJsonFragment(['name' => 'SMS Test']);
    }

    private function validate_voice_channel_features(): void
    {
        // Feature: Voice Channel Creation
        $channelData = [
            'name' => 'Voice Test',
            'phone_number' => '+1234567890',
            'provider' => 'twilio',
            'provider_config' => [
                'account_sid' => 'test_sid',
                'auth_token' => 'test_token'
            ]
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/channels/voice", $channelData);
        $response->assertStatus(201)->assertJsonFragment(['name' => 'Voice Test']);
    }

    private function validate_telegram_channel_features(): void
    {
        // Feature: Telegram Channel Creation
        $channelData = [
            'name' => 'Telegram Test',
            'bot_token' => 'test_bot_token'
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/channels/telegram", $channelData);
        $response->assertStatus(201)->assertJsonFragment(['name' => 'Telegram Test']);
    }

    private function validate_twitter_channel_features(): void
    {
        // Feature: Twitter Channel Creation
        $channelData = [
            'name' => 'Twitter Test',
            'consumer_key' => 'test_key',
            'consumer_secret' => 'test_secret',
            'access_token' => 'test_token',
            'access_token_secret' => 'test_token_secret'
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/channels/twitter", $channelData);
        $response->assertStatus(201)->assertJsonFragment(['name' => 'Twitter Test']);
    }

    private function validate_web_widget_features(): void
    {
        // Feature: Web Widget Configuration
        $response = $this->postJson('/api/v1/widget/config', [
            'website_token' => 'test_token',
            'inbox_identifier' => $this->inbox->identifier
        ]);
        $response->assertStatus(200);
        
        // Feature: Widget Conversations
        $response = $this->postJson('/api/v1/widget/conversations', [
            'website_token' => 'test_token',
            'contact' => [
                'name' => 'Widget User',
                'email' => 'widget@example.com'
            ]
        ]);
        $response->assertStatus(201);
    }

    // ========================================
    // Advanced Features Validation
    // ========================================

    private function validate_automation_rules_features(): void
    {
        // Feature: Automation Rule CRUD
        $ruleData = [
            'name' => 'Test Automation Rule',
            'description' => 'Test rule description',
            'event_name' => 'conversation_created',
            'conditions' => [
                [
                    'attribute_key' => 'status',
                    'filter_operator' => 'equal_to',
                    'values' => ['open']
                ]
            ],
            'actions' => [
                [
                    'action_name' => 'assign_agent',
                    'action_params' => ['agent_id' => $this->user->id]
                ]
            ]
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/automation_rules", $ruleData);
        $response->assertStatus(201)->assertJsonFragment(['name' => 'Test Automation Rule']);
        
        $ruleId = $response->json('data.id');
        
        // Feature: Automation Rule Clone
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/automation_rules/{$ruleId}/clone");
        $response->assertStatus(201);
    }

    private function validate_canned_responses_features(): void
    {
        // Feature: Canned Response CRUD
        $responseData = [
            'short_code' => 'greeting',
            'content' => 'Hello! How can I help you today?'
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/canned_responses", $responseData);
        $response->assertStatus(201)->assertJsonFragment(['short_code' => 'greeting']);
        
        // Feature: Canned Response Search
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/canned_responses?search=greeting");
        $response->assertStatus(200);
        
        $responses = $response->json('data');
        $this->assertNotEmpty($responses);
        $this->assertEquals('greeting', $responses[0]['short_code']);
    }

    private function validate_webhooks_features(): void
    {
        // Feature: Webhook CRUD
        $webhookData = [
            'url' => 'https://example.com/webhook',
            'subscriptions' => ['conversation_created', 'message_created']
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/webhooks", $webhookData);
        $response->assertStatus(201)->assertJsonFragment(['url' => 'https://example.com/webhook']);
    }

    private function validate_campaigns_features(): void
    {
        // Feature: Campaign CRUD
        $campaignData = [
            'title' => 'Test Campaign',
            'description' => 'Test campaign description',
            'message' => 'Hello from our campaign!',
            'inbox_id' => $this->inbox->id,
            'trigger_rules' => [
                'url' => 'https://example.com',
                'time_on_page' => 30
            ]
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/campaigns", $campaignData);
        $response->assertStatus(201)->assertJsonFragment(['title' => 'Test Campaign']);
    }

    private function validate_reports_features(): void
    {
        // Create test data for reports
        $contact = Contact::factory()->create(['account_id' => $this->account->id]);
        $conversation = Conversation::factory()->create([
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
            'contact_id' => $contact->id
        ]);
        
        // Feature: Conversation Reports
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/reports/conversations?" . http_build_query([
            'metric' => 'conversations_count',
            'type' => 'account',
            'since' => now()->subDays(7)->toDateString(),
            'until' => now()->toDateString()
        ]));
        $response->assertStatus(200)->assertJsonStructure(['data']);
        
        // Feature: Agent Reports
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/reports/agents?" . http_build_query([
            'metric' => 'avg_first_response_time',
            'type' => 'agent',
            'since' => now()->subDays(7)->toDateString(),
            'until' => now()->toDateString()
        ]));
        $response->assertStatus(200)->assertJsonStructure(['data']);
    }

    private function validate_search_features(): void
    {
        // Create test data
        $contact = Contact::factory()->create([
            'account_id' => $this->account->id,
            'name' => 'Searchable Contact'
        ]);
        
        $conversation = Conversation::factory()->create([
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
            'contact_id' => $contact->id
        ]);
        
        // Feature: Global Search
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/search?q=Searchable");
        $response->assertStatus(200)->assertJsonStructure(['data']);
        
        // Feature: Contact Search
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/contacts?q=Searchable");
        $response->assertStatus(200);
        
        $contacts = $response->json('data');
        $this->assertNotEmpty($contacts);
        $this->assertStringContainsString('Searchable', $contacts[0]['name']);
    }

    private function validate_bulk_actions_features(): void
    {
        // Create test conversations
        $contact = Contact::factory()->create(['account_id' => $this->account->id]);
        $conversations = Conversation::factory()->count(3)->create([
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
            'contact_id' => $contact->id,
            'status' => 'open'
        ]);
        
        $conversationIds = $conversations->pluck('id')->toArray();
        
        // Feature: Bulk Status Update
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/conversations/bulk_actions", [
            'type' => 'update_status',
            'ids' => $conversationIds,
            'status' => 'resolved'
        ]);
        $response->assertStatus(200);
        
        // Verify bulk update worked
        foreach ($conversationIds as $id) {
            $response = $this->getJson("/api/v1/accounts/{$this->account->id}/conversations/{$id}");
            $response->assertStatus(200)->assertJsonFragment(['status' => 'resolved']);
        }
    }

    // ========================================
    // Enterprise Features Validation
    // ========================================

    private function validate_sla_policies_features(): void
    {
        // Feature: SLA Policy CRUD
        $slaData = [
            'name' => 'Test SLA Policy',
            'description' => 'Test SLA policy description',
            'first_response_time_threshold' => 3600,
            'next_response_time_threshold' => 1800,
            'resolution_time_threshold' => 86400
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/sla_policies", $slaData);
        $response->assertStatus(201)->assertJsonFragment(['name' => 'Test SLA Policy']);
        
        $slaId = $response->json('data.id');
        
        // Feature: SLA Metrics
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/sla_policies/{$slaId}/metrics");
        $response->assertStatus(200);
    }

    private function validate_custom_roles_features(): void
    {
        // Feature: Custom Role Creation
        $roleData = [
            'name' => 'Custom Agent Role',
            'description' => 'Custom role for agents',
            'permissions' => [
                'conversation_manage',
                'contact_manage'
            ]
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/custom_roles", $roleData);
        $response->assertStatus(201)->assertJsonFragment(['name' => 'Custom Agent Role']);
    }

    private function validate_saml_sso_features(): void
    {
        // Feature: SAML Configuration
        $samlData = [
            'enabled' => true,
            'issuer' => 'https://example.com/saml',
            'sso_url' => 'https://example.com/saml/sso',
            'certificate' => 'test_certificate'
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/saml_settings", $samlData);
        $response->assertStatus(201)->assertJsonFragment(['enabled' => true]);
    }

    private function validate_audit_logs_features(): void
    {
        // Create some activity to generate audit logs
        $contact = Contact::factory()->create(['account_id' => $this->account->id]);
        
        // Feature: Audit Logs Listing
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/audit_logs");
        $response->assertStatus(200)->assertJsonStructure(['data']);
        
        // Feature: Audit Logs Filtering
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/audit_logs?user_id={$this->user->id}");
        $response->assertStatus(200);
    }

    // ========================================
    // Widget and Public APIs Validation
    // ========================================

    private function validate_widget_api_features(): void
    {
        // Feature: Widget Configuration
        $response = $this->postJson('/api/v1/widget/config', [
            'website_token' => 'test_token'
        ]);
        $response->assertStatus(200);
        
        // Feature: Widget Contact Management
        $response = $this->patchJson('/api/v1/widget/contact', [
            'website_token' => 'test_token',
            'name' => 'Widget User',
            'email' => 'widget@example.com'
        ]);
        $response->assertStatus(200);
        
        // Feature: Widget Conversations
        $response = $this->postJson('/api/v1/widget/conversations', [
            'website_token' => 'test_token'
        ]);
        $response->assertStatus(201);
    }

    private function validate_public_api_features(): void
    {
        // Feature: Public Contact Creation
        $response = $this->postJson("/api/v1/public/inboxes/{$this->inbox->identifier}/contacts", [
            'name' => 'Public Contact',
            'email' => 'public@example.com'
        ]);
        $response->assertStatus(201);
        
        $contactId = $response->json('data.id');
        
        // Feature: Public Conversation Creation
        $response = $this->postJson("/api/v1/public/inboxes/{$this->inbox->identifier}/conversations", [
            'contact_id' => $contactId,
            'message' => [
                'content' => 'Public message content'
            ]
        ]);
        $response->assertStatus(201);
    }

    private function validate_platform_api_features(): void
    {
        // Feature: Platform Account Management
        $response = $this->postJson('/api/v1/platform/accounts', [
            'name' => 'Platform Account'
        ]);
        $response->assertStatus(201);
        
        $accountId = $response->json('data.id');
        
        // Feature: Platform User Management
        $response = $this->postJson('/api/v1/platform/users', [
            'name' => 'Platform User',
            'email' => 'platform@example.com',
            'password' => 'password'
        ]);
        $response->assertStatus(201);
    }
}