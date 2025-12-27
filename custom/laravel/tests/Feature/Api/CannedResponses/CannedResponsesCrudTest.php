<?php

/**
 * Comprehensive Canned Response API Tests
 *
 * Tests all canned response-related API functionality including CRUD operations
 * and edge cases.
 */

use App\Models\Account;
use App\Models\CannedResponse;
use App\Models\User;

describe('Canned Response Listing', function () {
    test('can list canned responses for account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        CannedResponse::factory(10)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/canned_responses");

        $response->assertOk()
            ->assertJsonCount(10, 'data');
    });

    test('empty account returns empty canned responses list', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/canned_responses");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    test('canned responses list includes expected fields', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        CannedResponse::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/canned_responses");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'short_code',
                        'content',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    });

    test('can search canned responses by short code', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        CannedResponse::factory()->for($account)->create(['short_code' => 'greeting']);
        CannedResponse::factory()->for($account)->create(['short_code' => 'farewell']);
        CannedResponse::factory()->for($account)->create(['short_code' => 'greeting_vip']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/canned_responses?search=greeting");

        $response->assertOk();
        $codes = collect($response->json('data'))->pluck('short_code');
        expect($codes->filter(fn ($c) => str_contains($c, 'greeting'))->count())->toBeGreaterThanOrEqual(1);
    });
});

describe('Canned Response Creation', function () {
    test('can create canned response', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/canned_responses", [
                'short_code' => 'hello',
                'content' => 'Hello! Thank you for reaching out. How can I help you today?',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.short_code', 'hello')
            ->assertJsonPath('data.content', 'Hello! Thank you for reaching out. How can I help you today?');
    });

    test('canned response creation requires short_code', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/canned_responses", [
                'content' => 'Some content',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['short_code']);
    });

    test('canned response creation requires content', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/canned_responses", [
                'short_code' => 'test',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['content']);
    });

    test('duplicate short_code within account fails', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        CannedResponse::factory()->for($account)->create(['short_code' => 'existing']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/canned_responses", [
                'short_code' => 'existing',
                'content' => 'New content',
            ]);

        $response->assertUnprocessable();
    });
});

describe('Canned Response Retrieval', function () {
    test('can show canned response', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $cannedResponse = CannedResponse::factory()->for($account)->create([
            'short_code' => 'test',
            'content' => 'Test content',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/canned_responses/{$cannedResponse->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $cannedResponse->id)
            ->assertJsonPath('data.short_code', 'test');
    });

    test('cannot access canned response from other account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $otherAccount = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $cannedResponse = CannedResponse::factory()->for($otherAccount)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$otherAccount->id}/canned_responses/{$cannedResponse->id}");

        // User doesn't have access to otherAccount, middleware returns 404
        $response->assertNotFound();
    });
});

describe('Canned Response Update', function () {
    test('can update canned response', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $cannedResponse = CannedResponse::factory()->for($account)->create([
            'short_code' => 'original',
            'content' => 'Original content',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/canned_responses/{$cannedResponse->id}", [
                'short_code' => 'updated',
                'content' => 'Updated content',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.short_code', 'updated')
            ->assertJsonPath('data.content', 'Updated content');
    });

    test('can update only content', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $cannedResponse = CannedResponse::factory()->for($account)->create([
            'short_code' => 'greeting',
            'content' => 'Original greeting',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/canned_responses/{$cannedResponse->id}", [
                'content' => 'Updated greeting',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.short_code', 'greeting')
            ->assertJsonPath('data.content', 'Updated greeting');
    });
});

describe('Canned Response Deletion', function () {
    test('can delete canned response', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $cannedResponse = CannedResponse::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/canned_responses/{$cannedResponse->id}");

        $response->assertNoContent();
        expect(CannedResponse::find($cannedResponse->id))->toBeNull();
    });

    test('deleting non-existent canned response returns 404', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/canned_responses/99999");

        $response->assertNotFound();
    });
});

describe('Canned Response Authorization', function () {
    test('unauthenticated user cannot list canned responses', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/canned_responses");

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot create canned response', function () {
        $account = Account::factory()->create();

        $response = $this->postJson("/api/v1/accounts/{$account->id}/canned_responses", [
            'short_code' => 'test',
            'content' => 'Test content',
        ]);

        $response->assertUnauthorized();
    });
});

describe('Canned Response Edge Cases', function () {
    test('canned response with unicode content', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/canned_responses", [
                'short_code' => 'jp_greeting',
                'content' => 'こんにちは！ 👋 お問い合わせありがとうございます。',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.content', 'こんにちは！ 👋 お問い合わせありがとうございます。');
    });

    test('canned response with long content', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $longContent = str_repeat('This is a detailed response template. ', 50);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/canned_responses", [
                'short_code' => 'detailed',
                'content' => $longContent,
            ]);

        $response->assertCreated();
    });

    test('canned response with html content', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $htmlContent = '<p>Hello!</p><br><ul><li>Step 1</li><li>Step 2</li></ul>';

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/canned_responses", [
                'short_code' => 'html_response',
                'content' => $htmlContent,
            ]);

        $response->assertCreated();
    });

    test('handles many canned responses', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        CannedResponse::factory(100)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/canned_responses");

        $response->assertOk();
    });

    test('short_code with special characters', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/canned_responses", [
                'short_code' => 'greeting_v2.1-beta',
                'content' => 'Test content',
            ]);

        $response->assertCreated();
    });
});
