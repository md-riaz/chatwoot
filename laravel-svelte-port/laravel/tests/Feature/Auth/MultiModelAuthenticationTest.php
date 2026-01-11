<?php

namespace Tests\Feature\Auth;

use App\Models\Account;
use App\Models\AgentBot;
use App\Models\PlatformApp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Multi-Model Authentication Tests
 * 
 * Tests that verify User, AgentBot, and PlatformApp models can all
 * authenticate using Sanctum tokens via the MultiModelSanctumGuard.
 */
class MultiModelAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test User authentication via Sanctum token (backward compatibility).
     */
    public function test_user_can_authenticate_with_sanctum_token(): void
    {
        // Create user and account
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        // Create a new token and get the plain text version
        $newToken = $user->createToken('test-token');
        $plainTextToken = $newToken->plainTextToken;

        // Make authenticated request with Bearer token
        $response = $this->withHeader('Authorization', 'Bearer ' . $plainTextToken)
            ->getJson('/api/v1/auth/me');

        $response->assertOk();
        $response->assertJsonPath('data.email', $user->email);
    }

    /**
     * Test User authentication via api_access_token header (Rails compatibility).
     */
    public function test_user_can_authenticate_with_api_access_token_header(): void
    {
        // Create user
        $user = User::factory()->create();

        // Create a new token and get the plain text version
        $newToken = $user->createToken('test-token');
        $plainTextToken = $newToken->plainTextToken;

        // Make authenticated request with custom header
        $response = $this->withHeader('api_access_token', $plainTextToken)
            ->getJson('/api/v1/auth/me');

        $response->assertOk();
        $response->assertJsonPath('data.email', $user->email);
    }

    /**
     * Test AgentBot authentication via Sanctum token.
     */
    public function test_agent_bot_can_authenticate_with_sanctum_token(): void
    {
        // Create bot and account
        $account = Account::factory()->create();
        $bot = AgentBot::factory()->for($account)->create();

        // Create a new token and get the plain text version
        $newToken = $bot->createToken('test-token');
        $plainTextToken = $newToken->plainTextToken;

        // Create a user to set up the account context
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        // Make authenticated request as bot with Bearer token
        // Bot should be authenticated but restricted to specific endpoints
        $response = $this->withHeader('Authorization', 'Bearer ' . $plainTextToken)
            ->postJson("/api/v1/accounts/{$account->id}/conversations", [
                'inbox_id' => 1,
                'contact_id' => 1,
            ]);

        // Bot should be authenticated (may fail on other validation, but not auth)
        $this->assertNotEquals(401, $response->status(), 'Bot should be authenticated');
    }

    /**
     * Test AgentBot access control - restricted endpoints.
     */
    public function test_agent_bot_cannot_access_restricted_endpoint(): void
    {
        // Create bot and account
        $account = Account::factory()->create();
        $bot = AgentBot::factory()->for($account)->create();

        // Create a new token and get the plain text version
        $newToken = $bot->createToken('test-token');
        $plainTextToken = $newToken->plainTextToken;

        // Try to access a restricted endpoint (e.g., auth/me which is not in bot accessible list)
        $response = $this->withHeader('Authorization', 'Bearer ' . $plainTextToken)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'Access to this endpoint is not authorized for bots'
        ]);
    }

    /**
     * Test PlatformApp authentication via Sanctum token.
     */
    public function test_platform_app_can_authenticate(): void
    {
        // Create platform app
        $platformApp = PlatformApp::factory()->create();

        // Create a new token and get the plain text version
        $newToken = $platformApp->createToken('test-token');
        $plainTextToken = $newToken->plainTextToken;

        // Make authenticated request to platform routes
        $response = $this->withHeader('Authorization', 'Bearer ' . $plainTextToken)
            ->getJson('/api/v1/platform/agent_bots');

        $response->assertOk();
    }

    /**
     * Test PlatformApp authentication with api_access_token header.
     */
    public function test_platform_app_can_authenticate_with_custom_header(): void
    {
        // Create platform app
        $platformApp = PlatformApp::factory()->create();

        // Create a new token and get the plain text version
        $newToken = $platformApp->createToken('test-token');
        $plainTextToken = $newToken->plainTextToken;

        // Make authenticated request with custom header
        $response = $this->withHeader('api_access_token', $plainTextToken)
            ->getJson('/api/v1/platform/agent_bots');

        $response->assertOk();
    }

    /**
     * Test PlatformApp cannot access platform routes without token.
     */
    public function test_platform_app_authentication_required(): void
    {
        // Try to access platform routes without authentication
        $response = $this->getJson('/api/v1/platform/agent_bots');

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'Invalid access_token'
        ]);
    }

    /**
     * Test User token cannot access platform routes.
     */
    public function test_user_token_cannot_access_platform_routes(): void
    {
        // Create user
        $user = User::factory()->create();

        // Create a new token and get the plain text version
        $newToken = $user->createToken('test-token');
        $plainTextToken = $newToken->plainTextToken;

        // Try to access platform routes with user token
        $response = $this->withHeader('Authorization', 'Bearer ' . $plainTextToken)
            ->getJson('/api/v1/platform/agent_bots');

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'Invalid access_token'
        ]);
    }

    /**
     * Test PlatformApp resource permissions validation.
     */
    public function test_platform_app_resource_permissions_validated(): void
    {
        // Create platform app
        $platformApp = PlatformApp::factory()->create();

        // Create an agent bot but DON'T add it to platform app permissibles
        $bot = AgentBot::factory()->create();

        // Create a new token and get the plain text version
        $newToken = $platformApp->createToken('test-token');
        $plainTextToken = $newToken->plainTextToken;

        // Try to access the bot (should fail - not in permissibles)
        $response = $this->withHeader('Authorization', 'Bearer ' . $plainTextToken)
            ->getJson("/api/v1/platform/agent_bots/{$bot->id}");

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'Non permissible resource'
        ]);
    }

    /**
     * Test PlatformApp can access permissible resources.
     */
    public function test_platform_app_can_access_permissible_resource(): void
    {
        // Create platform app
        $platformApp = PlatformApp::factory()->create();

        // Create an agent bot and add it to platform app permissibles
        $bot = AgentBot::factory()->create();
        $platformApp->permissibles()->create([
            'permissible_type' => get_class($bot),
            'permissible_id' => $bot->id,
        ]);

        // Create a new token and get the plain text version
        $newToken = $platformApp->createToken('test-token');
        $plainTextToken = $newToken->plainTextToken;

        // Access the bot (should succeed - it's in permissibles)
        $response = $this->withHeader('Authorization', 'Bearer ' . $plainTextToken)
            ->getJson("/api/v1/platform/agent_bots/{$bot->id}");

        $response->assertOk();
    }

    /**
     * Test invalid token returns 401.
     */
    public function test_invalid_token_returns_unauthorized(): void
    {
        // Try to authenticate with invalid token
        $response = $this->withHeader('Authorization', 'Bearer invalid-token-12345')
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    /**
     * Test HTTP_API_ACCESS_TOKEN header variant (Rails compatibility).
     */
    public function test_http_api_access_token_header_variant(): void
    {
        // Create user
        $user = User::factory()->create();

        // Create a new token and get the plain text version
        $newToken = $user->createToken('test-token');
        $plainTextToken = $newToken->plainTextToken;

        // Make authenticated request with HTTP_API_ACCESS_TOKEN header
        $response = $this->withHeader('HTTP_API_ACCESS_TOKEN', $plainTextToken)
            ->getJson('/api/v1/auth/me');

        $response->assertOk();
        $response->assertJsonPath('data.email', $user->email);
    }
}
