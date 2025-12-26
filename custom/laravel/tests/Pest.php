<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');
uses(TestCase::class)->in('Unit');

/**
 * Create and authenticate as a user.
 */
function actingAsUser(?User $user = null): TestCase
{
    $user ??= User::factory()->create();

    return test()->actingAs($user, 'sanctum');
}

/**
 * Create and authenticate as an admin user with access to an account.
 */
function actingAsAdmin(?Account $account = null): TestCase
{
    $account ??= Account::factory()->create();
    $admin = User::factory()->create();
    $account->users()->attach($admin->id, ['role' => 2]);

    return test()->actingAs($admin, 'sanctum');
}

/**
 * Create and authenticate as an agent user with access to an account.
 */
function actingAsAgent(?Account $account = null): TestCase
{
    $account ??= Account::factory()->create();
    $agent = User::factory()->create();
    $account->users()->attach($agent->id, ['role' => 1]);

    return test()->actingAs($agent, 'sanctum');
}

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});
