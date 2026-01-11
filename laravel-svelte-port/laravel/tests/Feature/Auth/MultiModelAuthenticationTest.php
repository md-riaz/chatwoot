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

        // Get the auto-created token
        $token = $user->tokens()->where('name', 'api-access')->first();
        $this->assertNotNull($token, 'User should have auto-created api-access token');

        // Make authenticated request with Bearer token
        $response = $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken ?? $token->token)
            ->getJson('/api/v1/auth/me');

        $response->assertOk();
        $response->assertJsonPath('email', $user->email);
    }

    /**
     * Test User authentication via api_access_token header (Rails compatibility).
     */
    public function test_user_can_authenticate_with_api_access_token_header(): void
    {
        // Create user
        $user = User::factory()->create();

        // Get the token
        $token = $user->tokens()->where('name', 'api-access')->first();
        $this->assertNotNull($token);

        // Make authenticated request with custom header
        $response = $this->withHeader('api_access_token', $token->token)
            ->getJson('/api/v1/auth/me');

        $response->assertOk();
        $response->assertJsonPath('email', $user->email);
    }

    /**
     * Test AgentBot authentication via Sanctum token.
     */
    public function test_agent_bot_can_authenticate_with_sanctum_token(): void
    {
        // Create bot and account
        $account = Account::factory()->create();
        $bot = AgentBot::factory()->for($account)->create();

        // Get the auto-created token
        $token = $bot->tokens()->where('name', 'api-access')->first();
        $this->assertNotNull($token, 'AgentBot should have auto-created api-access token');

        // Create a user to set up the account context
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        // Make authenticated request as bot with Bearer token
        $response = $this->withHeader('Authorization', 'Bearer ' . $token->token)
            ->postJson("/api/v1/accounts/{$account->id}/conversations", [
                'inbox_id' => 1,
                'contact_id' => 1,
            ]);

        // Bot should be authenticated (may fail on other validation, but not auth)
        $response->assertStatus(fn($status) => $status !== 401);
    }

    /**
     * Test AgentBot access control - restricted endpoints.
     */
    public function test_agent_bot_cannot_access_restricted_endpoint(): void
    {
        // Create bot and account
        $account = Account::factory()->create();
        $bot = AgentBot::factory()->for($account)->create();

        // Get the auto-created token
        $token = $bot->tokens()->where('name', 'api-access')->first();

        // Try to access a restricted endpoint (e.g., users list)
        $response = $this->withHeader('Authorization', 'Bearer ' . $token->token)
            ->getJson("/api/v1/accounts/{$account->id}/users");

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

        // Get the auto-created token
        $token = $platformApp->tokens()->where('name', 'api-access')->first();
        $this->assertNotNull($token, 'PlatformApp should have auto-created api-access token');

        // Make authenticated request to platform routes
        $response = $this->withHeader('Authorization', 'Bearer ' . $token->token)
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

        // Get the auto-created token
        $token = $platformApp->tokens()->where('name', 'api-access')->first();

        // Make authenticated request with custom header
        $response = $this->withHeader('api_access_token', $token->token)
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

        // Get user token
        $token = $user->tokens()->where('name', 'api-access')->first();

        // Try to access platform routes with user token
        $response = $this->withHeader('Authorization', 'Bearer ' . $token->token)
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

        // Get the platform app token
        $token = $platformApp->tokens()->where('name', 'api-access')->first();

        // Try to access the bot (should fail - not in permissibles)
        $response = $this->withHeader('Authorization', 'Bearer ' . $token->token)
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

        // Get the platform app token
        $token = $platformApp->tokens()->where('name', 'api-access')->first();

        // Access the bot (should succeed - it's in permissibles)
        $response = $this->withHeader('Authorization', 'Bearer ' . $token->token)
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

        // Get the token
        $token = $user->tokens()->where('name', 'api-access')->first();

        // Make authenticated request with HTTP_API_ACCESS_TOKEN header
        $response = $this->withHeader('HTTP_API_ACCESS_TOKEN', $token->token)
            ->getJson('/api/v1/auth/me');

        $response->assertOk();
        $response->assertJsonPath('email', $user->email);
    }
}
