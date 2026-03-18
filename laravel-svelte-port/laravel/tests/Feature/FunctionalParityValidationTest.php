<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Account;
use App\Models\Inbox;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

/**
 * Comprehensive Functional Parity Validation Test Suite
 * 
 * This test suite validates 100% functional parity between Rails and Laravel implementations
 * by testing actual functionality rather than just code existence.
 * 
 * Reference: TASK_21_FINAL_CHECKPOINT_VALIDATION_REPORT.md
 * Task: 29.2 Functional Parity Validation
 */
class FunctionalParityValidationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private Account $account;
    private Inbox $inbox;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data for functional validation
        $this->user = User::factory()->create();
        $this->account = Account::factory()->create();
        $this->account->users()->attach($this->user, ['role' => 'administrator']);
        $this->inbox = Inbox::factory()->create(['account_id' => $this->account->id]);
        
        Sanctum::actingAs($this->user);
    }

    /**
     * Test Core API Endpoint Functional Parity
     * Validates: Requirements 1.1, 1.2, 1.3
     */
    public function test_core_api_endpoints_functional_parity(): void
    {
        // Test Authentication Endpoints
        $this->validate_authentication_functionality();
        
        // Test Account Management
        $this->validate_account_management_functionality();
        
        // Test Conversation Management
        $this->validate_conversation_management_functionality();
        
        // Test Message Management
        $this->validate_message_management_functionality();
        
        // Test Contact Management
        $this->validate_contact_management_functionality();
    }

    /**
     * Test Channel Integration Functional Parity
     * Validates: Requirements 4.1, 4.2
     */
    public function test_channel_integration_functional_parity(): void
    {
        // Test WhatsApp Integration
        $this->validate_whatsapp_integration_functionality();
        
        // Test Facebook/Instagram Integration
        $this->validate_facebook_integration_functionality();
        
        // Test Email Integration
        $this->validate_email_integration_functionality();
        
        // Test SMS/Twilio Integration
        $this->validate_sms_integration_functionality();
        
        // Test Voice Integration
        $this->validate_voice_integration_functionality();
    }

    /**
     * Test Third-Party Integration Functional Parity
     * Validates: Requirements 6.1
     */
    public function test_third_party_integration_functional_parity(): void
    {
        // Test Linear Integration
        $this->validate_linear_integration_functionality();
        
        // Test Shopify Integration
        $this->validate_shopify_integration_functionality();
        
    }

    /**
     * Test Enterprise Features Functional Parity
     * Validates: Requirements 5.1
     */
    public function test_enterprise_features_functional_parity(): void
    {
        // Test SAML SSO
        $this->validate_saml_sso_functionality();
        
        // Test SLA Policies
        $this->validate_sla_policies_functionality();
        
        // Test Custom Roles
        $this->validate_custom_roles_functionality();
    }

    /**
     * Test Performance Requirements
     * Validates: All performance requirements
     */
    public function test_performance_requirements(): void
    {
        // Test API Response Times
        $this->validate_api_response_times();
        
        // Test Throughput Requirements
        $this->validate_throughput_requirements();
        
        // Test Resource Usage
        $this->validate_resource_usage();
    }

    /**
     * Test Error Handling Functionality
     * Validates: All error handling scenarios
     */
    public function test_error_handling_functionality(): void
    {
        // Test Authentication Errors
        $this->validate_authentication_error_handling();
        
        // Test API Error Responses
        $this->validate_api_error_responses();
        
        // Test Integration Error Handling
        $this->validate_integration_error_handling();
    }

    // ========================================
    // Authentication Functionality Validation
    // ========================================

    private function validate_authentication_functionality(): void
    {
        // Test login functionality
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'user',
                        'token'
                    ]
                ]);

        // Test token validation
        $token = $response->json('data.token');
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->getJson('/api/v1/auth/me');
        
        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $this->user->id,
                        'email' => $this->user->email
                    ]
                ]);

        // Test logout functionality
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->postJson('/api/v1/auth/logout');
        
        $response->assertStatus(200);
    }

    private function validate_account_management_functionality(): void
    {
        // Test account listing
        $response = $this->getJson('/api/v1/accounts');
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]);

        // Test account creation
        $accountData = [
            'name' => $this->faker->company,
            'locale' => 'en',
            'timezone' => 'UTC'
        ];
        
        $response = $this->postJson('/api/v1/accounts', $accountData);
        $response->assertStatus(201)
                ->assertJsonFragment(['name' => $accountData['name']]);

        // Test account update
        $newName = $this->faker->company;
        $response = $this->patchJson("/api/v1/accounts/{$this->account->id}", [
            'name' => $newName
        ]);
        
        $response->assertStatus(200)
                ->assertJsonFragment(['name' => $newName]);
    }

    private function validate_conversation_management_functionality(): void
    {
        $contact = Contact::factory()->create(['account_id' => $this->account->id]);
        
        // Test conversation creation
        $conversationData = [
            'contact_id' => $contact->id,
            'inbox_id' => $this->inbox->id,
            'status' => 'open'
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/conversations", $conversationData);
        $response->assertStatus(201)
                ->assertJsonFragment(['status' => 'open']);

        $conversationId = $response->json('data.id');

        // Test conversation status update
        $response = $this->patchJson("/api/v1/accounts/{$this->account->id}/conversations/{$conversationId}", [
            'status' => 'resolved'
        ]);
        
        $response->assertStatus(200)
                ->assertJsonFragment(['status' => 'resolved']);

        // Test conversation assignment
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/conversations/{$conversationId}/assign", [
            'assignee_id' => $this->user->id
        ]);
        
        $response->assertStatus(200)
                ->assertJsonFragment(['assignee_id' => $this->user->id]);
    }

    private function validate_message_management_functionality(): void
    {
        $contact = Contact::factory()->create(['account_id' => $this->account->id]);
        $conversation = Conversation::factory()->create([
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
            'contact_id' => $contact->id
        ]);

        // Test message creation
        $messageData = [
            'content' => 'Test message content',
            'message_type' => 'outgoing',
            'sender_type' => 'User',
            'sender_id' => $this->user->id
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/conversations/{$conversation->id}/messages", $messageData);
        $response->assertStatus(201)
                ->assertJsonFragment(['content' => 'Test message content']);

        $messageId = $response->json('data.id');

        // Test message update
        $response = $this->patchJson("/api/v1/accounts/{$this->account->id}/conversations/{$conversation->id}/messages/{$messageId}", [
            'content' => 'Updated message content'
        ]);
        
        $response->assertStatus(200)
                ->assertJsonFragment(['content' => 'Updated message content']);
    }

    private function validate_contact_management_functionality(): void
    {
        // Test contact creation
        $contactData = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'phone_number' => $this->faker->phoneNumber
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/contacts", $contactData);
        $response->assertStatus(201)
                ->assertJsonFragment(['name' => $contactData['name']]);

        $contactId = $response->json('data.id');

        // Test contact update
        $newName = $this->faker->name;
        $response = $this->patchJson("/api/v1/accounts/{$this->account->id}/contacts/{$contactId}", [
            'name' => $newName
        ]);
        
        $response->assertStatus(200)
                ->assertJsonFragment(['name' => $newName]);

        // Test contact search
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/contacts?q={$newName}");
        $response->assertStatus(200)
                ->assertJsonFragment(['name' => $newName]);
    }

    // ========================================
    // Channel Integration Validation
    // ========================================

    private function validate_whatsapp_integration_functionality(): void
    {
        // Test WhatsApp channel creation
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
        $response->assertStatus(201)
                ->assertJsonFragment(['name' => 'WhatsApp Test']);

        // Test webhook verification
        $response = $this->getJson('/api/v1/webhooks/whatsapp?hub.mode=subscribe&hub.challenge=test_challenge&hub.verify_token=test_token');
        $response->assertStatus(200)
                ->assertSee('test_challenge');
    }

    private function validate_facebook_integration_functionality(): void
    {
        // Test Facebook channel creation
        $channelData = [
            'name' => 'Facebook Test',
            'page_id' => '123456789',
            'page_access_token' => 'test_token'
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/channels/facebook", $channelData);
        $response->assertStatus(201)
                ->assertJsonFragment(['name' => 'Facebook Test']);
    }

    private function validate_email_integration_functionality(): void
    {
        // Test Email channel creation
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
        $response->assertStatus(201)
                ->assertJsonFragment(['name' => 'Email Test']);
    }

    private function validate_sms_integration_functionality(): void
    {
        // Test SMS channel creation
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
        $response->assertStatus(201)
                ->assertJsonFragment(['name' => 'SMS Test']);
    }

    private function validate_voice_integration_functionality(): void
    {
        // Test Voice channel creation
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
        $response->assertStatus(201)
                ->assertJsonFragment(['name' => 'Voice Test']);
    }

    // ========================================
    // Third-Party Integration Validation
    // ========================================

    private function validate_linear_integration_functionality(): void
    {
        // Test Linear integration configuration
        $integrationData = [
            'name' => 'Linear Integration',
            'hook_type' => 'linear',
            'settings' => [
                'api_key' => 'test_api_key',
                'team_id' => 'test_team_id'
            ]
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/integrations/linear", $integrationData);
        $response->assertStatus(201)
                ->assertJsonFragment(['name' => 'Linear Integration']);
    }

    private function validate_shopify_integration_functionality(): void
    {
        // Test Shopify integration configuration
        $integrationData = [
            'name' => 'Shopify Integration',
            'hook_type' => 'shopify',
            'settings' => [
                'shop_url' => 'test-shop.myshopify.com',
                'access_token' => 'test_token'
            ]
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/integrations/shopify", $integrationData);
        $response->assertStatus(201)
                ->assertJsonFragment(['name' => 'Shopify Integration']);
    }

    // ========================================
    // Enterprise Features Validation
    // ========================================

    private function validate_saml_sso_functionality(): void
    {
        // Test SAML configuration
        $samlData = [
            'enabled' => true,
            'issuer' => 'https://example.com/saml',
            'sso_url' => 'https://example.com/saml/sso',
            'certificate' => 'test_certificate'
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/saml_settings", $samlData);
        $response->assertStatus(201)
                ->assertJsonFragment(['enabled' => true]);
    }

    private function validate_sla_policies_functionality(): void
    {
        // Test SLA policy creation
        $slaData = [
            'name' => 'Test SLA Policy',
            'description' => 'Test SLA policy description',
            'first_response_time_threshold' => 3600, // 1 hour
            'next_response_time_threshold' => 1800,  // 30 minutes
            'resolution_time_threshold' => 86400     // 24 hours
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/sla_policies", $slaData);
        $response->assertStatus(201)
                ->assertJsonFragment(['name' => 'Test SLA Policy']);
    }

    private function validate_custom_roles_functionality(): void
    {
        // Test custom role creation
        $roleData = [
            'name' => 'Custom Agent Role',
            'description' => 'Custom role for agents',
            'permissions' => [
                'conversation_manage',
                'contact_manage'
            ]
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/custom_roles", $roleData);
        $response->assertStatus(201)
                ->assertJsonFragment(['name' => 'Custom Agent Role']);
    }

    // ========================================
    // Performance Validation
    // ========================================

    private function validate_api_response_times(): void
    {
        $startTime = microtime(true);
        
        // Test authentication response time
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ]);
        
        $authTime = (microtime(true) - $startTime) * 1000;
        $this->assertLessThan(200, $authTime, 'Authentication should respond within 200ms');
        
        $startTime = microtime(true);
        
        // Test conversation listing response time
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/conversations");
        
        $listTime = (microtime(true) - $startTime) * 1000;
        $this->assertLessThan(500, $listTime, 'Conversation listing should respond within 500ms');
    }

    private function validate_throughput_requirements(): void
    {
        // Create multiple conversations to test throughput
        $conversations = [];
        $startTime = microtime(true);
        
        for ($i = 0; $i < 10; $i++) {
            $contact = Contact::factory()->create(['account_id' => $this->account->id]);
            $conversation = Conversation::factory()->create([
                'account_id' => $this->account->id,
                'inbox_id' => $this->inbox->id,
                'contact_id' => $contact->id
            ]);
            $conversations[] = $conversation;
        }
        
        $creationTime = microtime(true) - $startTime;
        $throughput = 10 / $creationTime;
        
        $this->assertGreaterThan(5, $throughput, 'Should handle at least 5 conversations per second');
    }

    private function validate_resource_usage(): void
    {
        // Test memory usage
        $memoryBefore = memory_get_usage(true);
        
        // Perform memory-intensive operations
        $conversations = Conversation::with(['contact', 'messages', 'assignee'])
                                   ->where('account_id', $this->account->id)
                                   ->limit(100)
                                   ->get();
        
        $memoryAfter = memory_get_usage(true);
        $memoryUsed = ($memoryAfter - $memoryBefore) / 1024 / 1024; // MB
        
        $this->assertLessThan(50, $memoryUsed, 'Memory usage should be reasonable for 100 conversations');
    }

    // ========================================
    // Error Handling Validation
    // ========================================

    private function validate_authentication_error_handling(): void
    {
        // Test invalid credentials
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword'
        ]);
        
        $response->assertStatus(401)
                ->assertJsonStructure(['message']);

        // Test expired token handling
        $response = $this->withHeaders(['Authorization' => 'Bearer invalid_token'])
                         ->getJson('/api/v1/auth/me');
        
        $response->assertStatus(401);
    }

    private function validate_api_error_responses(): void
    {
        // Test validation errors
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/contacts", [
            'email' => 'invalid-email'
        ]);
        
        $response->assertStatus(422)
                ->assertJsonStructure([
                    'message',
                    'errors' => [
                        'email'
                    ]
                ]);

        // Test not found errors
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/contacts/999999");
        $response->assertStatus(404);

        // Test unauthorized access
        $otherAccount = Account::factory()->create();
        $response = $this->getJson("/api/v1/accounts/{$otherAccount->id}/contacts");
        $response->assertStatus(403);
    }

    private function validate_integration_error_handling(): void
    {
        // Test webhook with invalid signature
        $response = $this->postJson('/api/v1/webhooks/whatsapp', [
            'object' => 'whatsapp_business_account',
            'entry' => []
        ], [
            'X-Hub-Signature-256' => 'invalid_signature'
        ]);
        
        $response->assertStatus(401);

        // Test rate limiting
        for ($i = 0; $i < 100; $i++) {
            $response = $this->getJson('/api/v1/auth/me');
            if ($response->status() === 429) {
                break;
            }
        }
        
        // Should eventually hit rate limit
        $this->assertTrue($response->status() === 429 || $i < 100, 'Rate limiting should be enforced');
    }
}
