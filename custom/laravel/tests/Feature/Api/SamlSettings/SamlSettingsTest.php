<?php

/**
 * SAML Settings API Tests
 *
 * Tests SAML SSO settings CRUD functionality.
 */

use App\Models\Account;
use App\Models\AccountSamlSetting;
use App\Models\User;

describe('SAML Settings Show', function () {
    test('can show SAML settings for account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        AccountSamlSetting::factory()->for($account)->create([
            'sso_url' => 'https://idp.example.com/sso',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/saml_settings");

        $response->assertOk()
            ->assertJsonPath('data.sso_url', 'https://idp.example.com/sso');
    });

    test('returns null if no SAML settings configured', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/saml_settings");

        $response->assertOk()
            ->assertJsonPath('data', null);
    });
});

describe('SAML Settings Creation', function () {
    test('can create SAML settings', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/saml_settings", [
                'sso_url' => 'https://idp.example.com/sso',
                'certificate' => '-----BEGIN CERTIFICATE-----...',
                'sp_entity_id' => 'chatwoot-sp',
                'idp_entity_id' => 'https://idp.example.com',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.sso_url', 'https://idp.example.com/sso');
    });

    test('SAML settings creation requires sso_url', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/saml_settings", [
                'certificate' => '-----BEGIN CERTIFICATE-----...',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['sso_url']);
    });
});

describe('SAML Settings Update', function () {
    test('can update SAML settings', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        AccountSamlSetting::factory()->for($account)->create([
            'sso_url' => 'https://old.example.com/sso',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/saml_settings", [
                'sso_url' => 'https://new.example.com/sso',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.sso_url', 'https://new.example.com/sso');
    });
});

describe('SAML Settings Deletion', function () {
    test('can delete SAML settings', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        AccountSamlSetting::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/saml_settings");

        $response->assertNoContent();
        expect(AccountSamlSetting::where('account_id', $account->id)->count())->toBe(0);
    });
});

describe('SAML Settings Authorization', function () {
    test('unauthenticated user cannot access SAML settings', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/saml_settings");

        $response->assertUnauthorized();
    });
});
