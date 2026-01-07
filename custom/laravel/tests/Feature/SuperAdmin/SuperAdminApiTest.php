<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\User;
use App\Models\Account;
use App\Models\AccountUser;
use App\Models\InstallationConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuperAdminApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create super admin role
        Role::create(['name' => 'super_admin']);

        // Create super admin user
        $this->superAdmin = User::factory()->create([
            'email' => 'superadmin@example.com',
            'name' => 'Super Admin',
            'type' => 'SuperAdmin', // Use type field instead of role
        ]);
    }

    /** @test */
    public function super_admin_can_access_dashboard()
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->getJson('/api/v1/super_admin/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'accountsCount',
                    'usersCount',
                    'inboxesCount',
                    'conversationsCount',
                    'chartData'
                ]
            ]);
    }

    /** @test */
    public function super_admin_can_manage_accounts()
    {
        Sanctum::actingAs($this->superAdmin);

        // Create account
        $accountData = [
            'name' => 'Test Account',
            'domain' => 'test.example.com',
            'support_email' => 'support@test.example.com',
        ];

        $response = $this->postJson('/api/v1/super_admin/accounts', $accountData);
        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Test Account']);

        $accountId = $response->json('data.id');

        // List accounts
        $response = $this->getJson('/api/v1/super_admin/accounts');
        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta']);

        // Show account
        $response = $this->getJson("/api/v1/super_admin/accounts/{$accountId}");
        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Test Account']);

        // Update account
        $response = $this->putJson("/api/v1/super_admin/accounts/{$accountId}", [
            'name' => 'Updated Test Account',
        ]);
        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Test Account']);

        // Delete account
        $response = $this->deleteJson("/api/v1/super_admin/accounts/{$accountId}");
        $response->assertStatus(200);
    }

    /** @test */
    public function super_admin_can_manage_users()
    {
        Sanctum::actingAs($this->superAdmin);

        // Create user
        $userData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'Password123!',
            'role' => 'agent',
        ];

        $response = $this->postJson('/api/v1/super_admin/users', $userData);
        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Test User']);

        $userId = $response->json('data.id');

        // List users
        $response = $this->getJson('/api/v1/super_admin/users');
        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta']);

        // Show user
        $response = $this->getJson("/api/v1/super_admin/users/{$userId}");
        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Test User']);

        // Update user
        $response = $this->putJson("/api/v1/super_admin/users/{$userId}", [
            'name' => 'Updated Test User',
        ]);
        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Test User']);

        // Delete user
        $response = $this->deleteJson("/api/v1/super_admin/users/{$userId}");
        $response->assertStatus(204);
    }

    /** @test */
    public function super_admin_can_manage_account_users()
    {
        Sanctum::actingAs($this->superAdmin);

        $account = Account::factory()->create();
        $user = User::factory()->create();

        // Create account user relationship
        $accountUserData = [
            'user_id' => $user->id,
            'account_id' => $account->id,
            'role' => 'agent',
        ];

        $response = $this->postJson('/api/v1/super_admin/account_users', $accountUserData);
        $response->assertStatus(201)
            ->assertJsonFragment(['role_name' => 'agent']);

        $accountUserId = $response->json('data.id');

        // List account users
        $response = $this->getJson('/api/v1/super_admin/account_users');
        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta']);

        // Show account user
        $response = $this->getJson("/api/v1/super_admin/account_users/{$accountUserId}");
        $response->assertStatus(200)
            ->assertJsonFragment(['role_name' => 'agent']);

        // Update account user
        $response = $this->putJson("/api/v1/super_admin/account_users/{$accountUserId}", [
            'role' => 'admin',
        ]);
        $response->assertStatus(200)
            ->assertJsonFragment(['role_name' => 'admin']);

        // Delete account user
        $response = $this->deleteJson("/api/v1/super_admin/account_users/{$accountUserId}");
        $response->assertStatus(200);
    }

    /** @test */
    public function super_admin_can_manage_settings()
    {
        Sanctum::actingAs($this->superAdmin);

        // Create setting
        InstallationConfig::create([
            'name' => 'test_setting',
            'serialized_value' => 'test_value',
            'locked' => false,
        ]);

        // List settings
        $response = $this->getJson('/api/v1/super_admin/settings');
        $response->assertStatus(200)
            ->assertJsonStructure(['data']);

        // Update settings
        $response = $this->patchJson('/api/v1/super_admin/settings', [
            'settings' => [
                'test_setting' => 'updated_value',
            ],
        ]);
        $response->assertStatus(200);

        // Create new setting
        $response = $this->postJson('/api/v1/super_admin/settings', [
            'name' => 'new_setting',
            'value' => 'new_value',
        ]);
        $response->assertStatus(201);
    }

    /** @test */
    public function super_admin_can_manage_cache()
    {
        Sanctum::actingAs($this->superAdmin);

        // Get cache info
        $response = $this->getJson('/api/v1/super_admin/cache');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'default_driver',
                    'stores',
                ]
            ]);

        // Clear all cache
        $response = $this->postJson('/api/v1/super_admin/cache/clear');
        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'All cache cleared successfully']);

        // Clear specific cache type
        $response = $this->postJson('/api/v1/super_admin/cache/clear/application');
        $response->assertStatus(200);

        // Warm up cache
        $response = $this->postJson('/api/v1/super_admin/cache/warmup');
        $response->assertStatus(200);
    }

    /** @test */
    public function super_admin_can_view_audit_logs()
    {
        Sanctum::actingAs($this->superAdmin);

        // List audit logs
        $response = $this->getJson('/api/v1/super_admin/audit_logs');
        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta']);

        // Get audit stats
        $response = $this->getJson('/api/v1/super_admin/audit_logs/stats');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_audits',
                    'by_event',
                    'by_model',
                    'by_user',
                    'by_date',
                ]
            ]);
    }

    /** @test */
    public function super_admin_can_view_instance_status()
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->getJson('/api/v1/super_admin/instance_status');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'clearline_version',
                    'laravel_version',
                    'php_version',
                    'edition',
                    'database',
                    'redis',
                    'queue',
                    'migrations',
                    'system',
                ]
            ]);
    }

    /** @test */
    public function non_super_admin_cannot_access_super_admin_routes()
    {
        $regularUser = User::factory()->create();
        Sanctum::actingAs($regularUser);

        $routes = [
            '/api/v1/super_admin/dashboard',
            '/api/v1/super_admin/accounts',
            '/api/v1/super_admin/users',
            '/api/v1/super_admin/settings',
            '/api/v1/super_admin/cache',
            '/api/v1/super_admin/audit_logs',
            '/api/v1/super_admin/instance_status',
        ];

        foreach ($routes as $route) {
            $response = $this->getJson($route);
            $response->assertStatus(403)
                ->assertJsonFragment(['error' => 'Unauthorized. Super admin access required.']);
        }
    }

    /** @test */
    public function unauthenticated_user_cannot_access_super_admin_routes()
    {
        $routes = [
            '/api/v1/super_admin/dashboard',
            '/api/v1/super_admin/accounts',
            '/api/v1/super_admin/users',
            '/api/v1/super_admin/settings',
            '/api/v1/super_admin/cache',
            '/api/v1/super_admin/audit_logs',
            '/api/v1/super_admin/instance_status',
        ];

        foreach ($routes as $route) {
            $response = $this->getJson($route);
            $response->assertStatus(401);
        }
    }
}