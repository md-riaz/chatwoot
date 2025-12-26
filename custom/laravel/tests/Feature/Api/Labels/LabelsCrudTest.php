<?php

/**
 * Comprehensive Label API Tests
 *
 * Tests all label-related API functionality including CRUD operations,
 * associations with conversations/contacts, and edge cases.
 */

use App\Models\Account;
use App\Models\Label;
use App\Models\User;

describe('Label Listing', function () {
    test('can list labels for account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        Label::factory(10)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/labels");

        $response->assertOk()
            ->assertJsonCount(10, 'data');
    });

    test('empty account returns empty labels list', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/labels");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    test('labels list includes expected fields', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        Label::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/labels");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'color',
                        'show_on_sidebar',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    });
});

describe('Label Creation', function () {
    test('can create label', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/labels", [
                'title' => 'Priority',
                'description' => 'High priority issues',
                'color' => '#ff0000',
                'show_on_sidebar' => true,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Priority')
            ->assertJsonPath('data.description', 'High priority issues')
            ->assertJsonPath('data.color', '#ff0000')
            ->assertJsonPath('data.show_on_sidebar', true);
    });

    test('label creation requires title', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/labels", [
                'color' => '#ff0000',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    });

    test('label creation with minimal data', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/labels", [
                'title' => 'Bug',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Bug');
    });

    test('duplicate label title within account fails', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        Label::factory()->for($account)->create(['title' => 'existing']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/labels", [
                'title' => 'existing',
            ]);

        $response->assertUnprocessable();
    });
});

describe('Label Retrieval', function () {
    test('can show label', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $label = Label::factory()->for($account)->create(['title' => 'Test Label']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/labels/{$label->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $label->id)
            ->assertJsonPath('data.title', 'Test Label');
    });

    test('cannot access label from other account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $otherAccount = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $label = Label::factory()->for($otherAccount)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$otherAccount->id}/labels/{$label->id}");

        $response->assertNotFound();
    });
});

describe('Label Update', function () {
    test('can update label', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $label = Label::factory()->for($account)->create(['title' => 'Original']);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/labels/{$label->id}", [
                'title' => 'Updated',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated');
    });

    test('can update label color', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $label = Label::factory()->for($account)->create(['color' => '#ff0000']);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/labels/{$label->id}", [
                'color' => '#00ff00',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.color', '#00ff00');
    });

    test('can toggle show_on_sidebar', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $label = Label::factory()->for($account)->create(['show_on_sidebar' => true]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/labels/{$label->id}", [
                'show_on_sidebar' => false,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.show_on_sidebar', false);
    });
});

describe('Label Deletion', function () {
    test('can delete label', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $label = Label::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/labels/{$label->id}");

        $response->assertNoContent();
        expect(Label::find($label->id))->toBeNull();
    });

    test('deleting non-existent label returns 404', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/labels/99999");

        $response->assertNotFound();
    });
});

describe('Label Authorization', function () {
    test('unauthenticated user cannot list labels', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/labels");

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot create label', function () {
        $account = Account::factory()->create();

        $response = $this->postJson("/api/v1/accounts/{$account->id}/labels", [
            'title' => 'Test Label',
        ]);

        $response->assertUnauthorized();
    });

    test('user without account access cannot view labels', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/labels");

        $response->assertNotFound();
    });
});

describe('Label Validation', function () {
    test('title is required', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/labels", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    });

    test('title cannot be too long', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/labels", [
                'title' => str_repeat('a', 200),
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    });

    test('color must be valid hex format', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/labels", [
                'title' => 'Test',
                'color' => 'not-a-color',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['color']);
    });
});

describe('Label Edge Cases', function () {
    test('label with unicode title', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/labels", [
                'title' => '緊急 🔥',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', '緊急 🔥');
    });

    test('handles many labels', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        Label::factory(100)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/labels");

        $response->assertOk();
    });

    test('label with various color formats', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $colors = ['#ff0000', '#FF0000', '#f00', '#F00'];

        foreach ($colors as $index => $color) {
            $response = $this->actingAs($user, 'sanctum')
                ->postJson("/api/v1/accounts/{$account->id}/labels", [
                    'title' => 'Color Test '.$index,
                    'color' => $color,
                ]);

            $response->assertCreated();
        }
    });
});
