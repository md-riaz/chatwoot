<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Account;
use App\Models\AccountUser;
use App\Enums\AccountUserRole;
use App\Enums\UserAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_correct_user_format_for_superadmin()
    {
        // Create account
        $account = Account::factory()->create(['name' => 'Test Company']);
        
        // Create SuperAdmin user
        $user = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'type' => 'SuperAdmin',
            'email_verified_at' => now(),
        ]);
        
        // Link user to account as administrator
        AccountUser::create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'role' => AccountUserRole::ADMINISTRATOR,
            'availability' => UserAvailability::ONLINE,
        ]);

        // Test login
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'user' => [
                    'id', 'name', 'email', 'type', 'confirmed', 'locked', 'roles', 'accounts'
                ],
                'token'
            ]);

        $userData = $response->json('user');
        
        // Verify SuperAdmin type
        $this->assertEquals('SuperAdmin', $userData['type']);
        
        // Verify Rails parity fields
        $this->assertTrue($userData['confirmed']);
        $this->assertFalse($userData['locked']);
        
        // Verify account relationship
        $this->assertCount(1, $userData['accounts']);
        $this->assertEquals($account->id, $userData['accounts'][0]['id']);
        $this->assertEquals('Test Company', $userData['accounts'][0]['name']);
        $this->assertEquals('administrator', $userData['accounts'][0]['role']); // String, not enum
        $this->assertEquals('online', $userData['accounts'][0]['availability']); // String, not enum
    }

    public function test_me_endpoint_returns_correct_user_format()
    {
        // Create account
        $account = Account::factory()->create(['name' => 'Test Company']);
        
        // Create SuperAdmin user
        $user = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@test.com',
            'type' => 'SuperAdmin',
            'email_verified_at' => now(),
        ]);
        
        // Link user to account as administrator
        AccountUser::create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'role' => AccountUserRole::ADMINISTRATOR,
            'availability' => UserAvailability::ONLINE,
        ]);

        // Test /auth/me endpoint
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/me');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'email', 'type', 'confirmed', 'locked', 'roles', 'accounts'
                ]
            ]);
        
        $userData = $response->json('data'); // Get the data key
        
        // Verify SuperAdmin type
        $this->assertEquals('SuperAdmin', $userData['type']);
        
        // Verify Rails parity fields
        $this->assertTrue($userData['confirmed']);
        $this->assertFalse($userData['locked']);
        
        // Verify account relationship with proper enum conversion
        $this->assertCount(1, $userData['accounts']);
        $this->assertEquals($account->id, $userData['accounts'][0]['id']);
        $this->assertEquals('Test Company', $userData['accounts'][0]['name']);
        $this->assertEquals('administrator', $userData['accounts'][0]['role']); // String, not enum
        $this->assertEquals('online', $userData['accounts'][0]['availability']); // String, not enum
    }
}