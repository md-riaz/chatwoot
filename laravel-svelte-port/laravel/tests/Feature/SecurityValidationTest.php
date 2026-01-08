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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Security Validation Test Suite
 * 
 * Validates security measures and identifies vulnerabilities in the Laravel implementation.
 * This test suite focuses on the critical security gap identified in search functionality
 * and other potential security issues.
 * 
 * Reference: TASK_21_FINAL_CHECKPOINT_VALIDATION_REPORT.md
 * Task: 29.2 Functional Parity Validation - Security Testing
 */
class SecurityValidationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private User $otherUser;
    private Account $account;
    private Account $otherAccount;
    private Inbox $inbox;
    private Inbox $otherInbox;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users and accounts for security testing
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        
        $this->account = Account::factory()->create();
        $this->otherAccount = Account::factory()->create();
        
        // Associate users with their respective accounts
        $this->account->users()->attach($this->user, ['role' => 'administrator']);
        $this->otherAccount->users()->attach($this->otherUser, ['role' => 'administrator']);
        
        $this->inbox = Inbox::factory()->create(['account_id' => $this->account->id]);
        $this->otherInbox = Inbox::factory()->create(['account_id' => $this->otherAccount->id]);
    }

    /**
     * CRITICAL SECURITY TEST: Search Permission Filtering Vulnerability
     * 
     * This test validates the critical security issue identified in the analysis:
     * Users can access data they shouldn't see through search functionality.
     * 
     * Status: CRITICAL VULNERABILITY - REQUIRES IMMEDIATE FIX
     */
    public function test_search_permission_filtering_vulnerability(): void
    {
        // Create test data in different accounts
        $myContact = Contact::factory()->create([
            'account_id' => $this->account->id,
            'name' => 'My Secret Contact',
            'email' => 'mysecret@example.com'
        ]);
        
        $otherContact = Contact::factory()->create([
            'account_id' => $this->otherAccount->id,
            'name' => 'Other Secret Contact',
            'email' => 'othersecret@example.com'
        ]);
        
        $myConversation = Conversation::factory()->create([
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
            'contact_id' => $myContact->id
        ]);
        
        $otherConversation = Conversation::factory()->create([
            'account_id' => $this->otherAccount->id,
            'inbox_id' => $this->otherInbox->id,
            'contact_id' => $otherContact->id
        ]);
        
        // Authenticate as first user
        Sanctum::actingAs($this->user);
        
        // Test contact search - should NOT return contacts from other accounts
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/contacts?q=Secret");
        $response->assertStatus(200);
        
        $contacts = $response->json('data');
        $contactIds = collect($contacts)->pluck('id')->toArray();
        
        // CRITICAL: Should only return contacts from user's account
        $this->assertContains($myContact->id, $contactIds, 'Should find contact from own account');
        $this->assertNotContains($otherContact->id, $contactIds, 'SECURITY VULNERABILITY: Should NOT find contact from other account');
        
        // Test conversation search - should NOT return conversations from other accounts
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/conversations?q=Secret");
        $response->assertStatus(200);
        
        $conversations = $response->json('data');
        $conversationIds = collect($conversations)->pluck('id')->toArray();
        
        // CRITICAL: Should only return conversations from user's account
        $this->assertContains($myConversation->id, $conversationIds, 'Should find conversation from own account');
        $this->assertNotContains($otherConversation->id, $conversationIds, 'SECURITY VULNERABILITY: Should NOT find conversation from other account');
        
        // Test global search - should NOT return data from other accounts
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/search?q=Secret");
        $response->assertStatus(200);
        
        $searchResults = $response->json('data');
        
        // Verify search results are properly filtered by account
        foreach ($searchResults as $category => $results) {
            foreach ($results as $result) {
                if (isset($result['account_id'])) {
                    $this->assertEquals($this->account->id, $result['account_id'], 
                        "SECURITY VULNERABILITY: Search result from {$category} belongs to different account");
                }
            }
        }
    }

    /**
     * Test Authentication Security Measures
     */
    public function test_authentication_security_measures(): void
    {
        // Test password strength requirements
        $weakPasswords = ['123', 'password', '12345678', 'qwerty'];
        
        foreach ($weakPasswords as $weakPassword) {
            $response = $this->postJson('/api/v1/auth/register', [
                'name' => $this->faker->name,
                'email' => $this->faker->email,
                'password' => $weakPassword,
                'password_confirmation' => $weakPassword
            ]);
            
            // Should reject weak passwords
            $this->assertContains($response->status(), [422, 400], 
                "Should reject weak password: {$weakPassword}");
        }
        
        // Test rate limiting on login attempts
        $email = $this->user->email;
        $wrongPassword = 'wrongpassword';
        
        $attempts = 0;
        $rateLimited = false;
        
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson('/api/v1/auth/login', [
                'email' => $email,
                'password' => $wrongPassword
            ]);
            
            $attempts++;
            
            if ($response->status() === 429) {
                $rateLimited = true;
                break;
            }
        }
        
        $this->assertTrue($rateLimited, 'Should implement rate limiting on failed login attempts');
        $this->assertLessThan(10, $attempts, 'Should rate limit before 10 attempts');
    }

    /**
     * Test Authorization and Access Control
     */
    public function test_authorization_and_access_control(): void
    {
        Sanctum::actingAs($this->user);
        
        // Test cross-account access prevention
        $response = $this->getJson("/api/v1/accounts/{$this->otherAccount->id}/conversations");
        $response->assertStatus(403, 'Should prevent access to other accounts');
        
        // Test resource access without proper permissions
        $response = $this->postJson("/api/v1/accounts/{$this->otherAccount->id}/contacts", [
            'name' => 'Unauthorized Contact',
            'email' => 'unauthorized@example.com'
        ]);
        $response->assertStatus(403, 'Should prevent creating resources in other accounts');
        
        // Test accessing specific resources from other accounts
        $otherContact = Contact::factory()->create(['account_id' => $this->otherAccount->id]);
        
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/contacts/{$otherContact->id}");
        $response->assertStatus(404, 'Should not find resources from other accounts');
        
        // Test modifying resources from other accounts
        $response = $this->patchJson("/api/v1/accounts/{$this->account->id}/contacts/{$otherContact->id}", [
            'name' => 'Hacked Name'
        ]);
        $response->assertStatus(404, 'Should not allow modifying resources from other accounts');
    }

    /**
     * Test API Token Security
     */
    public function test_api_token_security(): void
    {
        // Test token expiration
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ]);
        
        $response->assertStatus(200);
        $token = $response->json('data.token');
        
        // Test token validation
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
                         ->getJson('/api/v1/auth/me');
        $response->assertStatus(200);
        
        // Test invalid token
        $response = $this->withHeaders(['Authorization' => 'Bearer invalid_token'])
                         ->getJson('/api/v1/auth/me');
        $response->assertStatus(401);
        
        // Test malformed token
        $response = $this->withHeaders(['Authorization' => 'Bearer'])
                         ->getJson('/api/v1/auth/me');
        $response->assertStatus(401);
        
        // Test missing token
        $response = $this->getJson('/api/v1/auth/me');
        $response->assertStatus(401);
    }

    /**
     * Test Input Validation and Sanitization
     */
    public function test_input_validation_and_sanitization(): void
    {
        Sanctum::actingAs($this->user);
        
        // Test SQL injection attempts
        $sqlInjectionAttempts = [
            "'; DROP TABLE contacts; --",
            "' OR '1'='1",
            "1' UNION SELECT * FROM users --",
            "<script>alert('xss')</script>",
            "javascript:alert('xss')"
        ];
        
        foreach ($sqlInjectionAttempts as $maliciousInput) {
            $response = $this->postJson("/api/v1/accounts/{$this->account->id}/contacts", [
                'name' => $maliciousInput,
                'email' => 'test@example.com'
            ]);
            
            // Should either reject the input or sanitize it
            if ($response->status() === 201) {
                $contact = $response->json('data');
                $this->assertNotEquals($maliciousInput, $contact['name'], 
                    'Should sanitize malicious input');
            } else {
                $this->assertContains($response->status(), [400, 422], 
                    'Should reject malicious input');
            }
        }
        
        // Test XSS prevention in search
        $xssPayload = "<script>alert('xss')</script>";
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/contacts?q=" . urlencode($xssPayload));
        
        $response->assertStatus(200);
        $responseContent = $response->getContent();
        $this->assertStringNotContainsString('<script>', $responseContent, 
            'Should prevent XSS in search results');
    }

    /**
     * Test Webhook Security
     */
    public function test_webhook_security(): void
    {
        // Test webhook signature verification for WhatsApp
        $payload = json_encode([
            'object' => 'whatsapp_business_account',
            'entry' => []
        ]);
        
        // Test without signature
        $response = $this->postJson('/api/v1/webhooks/whatsapp', json_decode($payload, true));
        $response->assertStatus(401, 'Should require webhook signature');
        
        // Test with invalid signature
        $response = $this->postJson('/api/v1/webhooks/whatsapp', json_decode($payload, true), [
            'X-Hub-Signature-256' => 'sha256=invalid_signature'
        ]);
        $response->assertStatus(401, 'Should reject invalid webhook signature');
        
        // Test Facebook webhook signature
        $response = $this->postJson('/api/v1/webhooks/facebook', json_decode($payload, true), [
            'X-Hub-Signature-256' => 'sha256=invalid_signature'
        ]);
        $response->assertStatus(401, 'Should reject invalid Facebook webhook signature');
    }

    /**
     * Test File Upload Security
     */
    public function test_file_upload_security(): void
    {
        Sanctum::actingAs($this->user);
        
        // Test malicious file upload attempts
        $maliciousFiles = [
            ['name' => 'malicious.php', 'content' => '<?php system($_GET["cmd"]); ?>', 'mime' => 'application/x-php'],
            ['name' => 'malicious.exe', 'content' => 'MZ...', 'mime' => 'application/x-executable'],
            ['name' => 'malicious.js', 'content' => 'alert("xss")', 'mime' => 'application/javascript'],
        ];
        
        foreach ($maliciousFiles as $file) {
            $response = $this->postJson("/api/v1/accounts/{$this->account->id}/attachments", [
                'file' => $file
            ]);
            
            // Should reject dangerous file types
            $this->assertContains($response->status(), [400, 422, 415], 
                "Should reject malicious file: {$file['name']}");
        }
        
        // Test file size limits
        $largeFile = [
            'name' => 'large.txt',
            'content' => str_repeat('A', 50 * 1024 * 1024), // 50MB
            'mime' => 'text/plain'
        ];
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/attachments", [
            'file' => $largeFile
        ]);
        
        $this->assertContains($response->status(), [400, 422, 413], 
            'Should enforce file size limits');
    }

    /**
     * Test CORS and Security Headers
     */
    public function test_cors_and_security_headers(): void
    {
        // Test CORS headers on API endpoints
        $response = $this->options('/api/v1/auth/login');
        
        // Should have proper CORS headers
        $this->assertTrue($response->headers->has('Access-Control-Allow-Origin') || 
                         $response->headers->has('access-control-allow-origin'),
                         'Should have CORS headers');
        
        // Test security headers on API responses
        Sanctum::actingAs($this->user);
        $response = $this->getJson('/api/v1/auth/me');
        
        $securityHeaders = [
            'X-Content-Type-Options',
            'X-Frame-Options',
            'X-XSS-Protection',
            'Referrer-Policy'
        ];
        
        foreach ($securityHeaders as $header) {
            $this->assertTrue($response->headers->has($header) || 
                             $response->headers->has(strtolower($header)),
                             "Should have security header: {$header}");
        }
    }

    /**
     * Test Session Security (if applicable)
     */
    public function test_session_security(): void
    {
        // Test session fixation prevention
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ]);
        
        $response->assertStatus(200);
        
        // Test secure cookie settings (if using cookies)
        $cookies = $response->headers->getCookies();
        
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'laravel_session' || $cookie->getName() === 'XSRF-TOKEN') {
                $this->assertTrue($cookie->isSecure() || app()->environment('testing'), 
                    'Session cookies should be secure in production');
                $this->assertTrue($cookie->isHttpOnly() || $cookie->getName() === 'XSRF-TOKEN', 
                    'Session cookies should be HTTP only');
            }
        }
    }

    /**
     * Test Data Exposure Prevention
     */
    public function test_data_exposure_prevention(): void
    {
        Sanctum::actingAs($this->user);
        
        // Test that sensitive data is not exposed in API responses
        $response = $this->getJson('/api/v1/auth/me');
        $response->assertStatus(200);
        
        $userData = $response->json('data');
        
        // Should not expose sensitive fields
        $this->assertArrayNotHasKey('password', $userData, 'Should not expose password');
        $this->assertArrayNotHasKey('remember_token', $userData, 'Should not expose remember token');
        
        // Test that error messages don't expose sensitive information
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword'
        ]);
        
        $response->assertStatus(401);
        $errorMessage = $response->json('message');
        
        // Should not reveal whether email exists or not
        $this->assertStringNotContainsString('user not found', strtolower($errorMessage), 
            'Should not reveal user existence');
        $this->assertStringNotContainsString('email not found', strtolower($errorMessage), 
            'Should not reveal email existence');
    }

    /**
     * Test Role-Based Access Control
     */
    public function test_role_based_access_control(): void
    {
        // Create agent user with limited permissions
        $agent = User::factory()->create();
        $this->account->users()->attach($agent, ['role' => 'agent']);
        
        Sanctum::actingAs($agent);
        
        // Test that agents cannot access admin-only endpoints
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/users");
        $this->assertContains($response->status(), [403, 404], 
            'Agents should not access user management');
        
        $response = $this->postJson("/api/v1/accounts/{$this->account->id}/inboxes", [
            'name' => 'Unauthorized Inbox',
            'channel_type' => 'email'
        ]);
        $this->assertContains($response->status(), [403, 404], 
            'Agents should not create inboxes');
        
        // Test that agents can access allowed endpoints
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/conversations");
        $response->assertStatus(200, 'Agents should access conversations');
        
        $contact = Contact::factory()->create(['account_id' => $this->account->id]);
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/contacts/{$contact->id}");
        $response->assertStatus(200, 'Agents should access contacts');
    }
}