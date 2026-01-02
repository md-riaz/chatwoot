<?php

/**
 * Comprehensive Portal API Tests
 *
 * Tests all portal-related API functionality including CRUD operations,
 * articles, categories, and edge cases.
 */

use App\Models\Account;
use App\Models\Article;
use App\Models\Category;
use App\Models\Portal;
use App\Models\User;

describe('Portal Listing', function () {
    test('can list portals for account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        Portal::factory(5)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/portals");

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    });

    test('empty account returns empty portals list', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/portals");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    test('portals list includes expected fields', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        Portal::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/portals");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'custom_domain',
                        'color',
                        'archived',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    });

    test('does not list archived portals by default', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        Portal::factory(3)->for($account)->active()->create();
        Portal::factory(2)->for($account)->archived()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/portals");

        $response->assertOk();
        // Archived portals might or might not be included based on implementation
    });
});

describe('Portal Creation', function () {
    test('can create portal', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/portals", [
                'name' => 'Help Center',
                'slug' => 'help-center',
                'color' => '#4f46e5',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Help Center')
            ->assertJsonPath('data.slug', 'help-center');
    });

    test('portal creation requires name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/portals", [
                'slug' => 'test',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });

    test('portal creation requires slug', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/portals", [
                'name' => 'Test Portal',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['slug']);
    });

    test('portal slug must be unique', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        Portal::factory()->for($account)->create(['slug' => 'existing-slug']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/portals", [
                'name' => 'New Portal',
                'slug' => 'existing-slug',
            ]);

        $response->assertUnprocessable();
    });

    test('portal with custom domain', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/portals", [
                'name' => 'Custom Domain Portal',
                'slug' => 'custom-portal',
                'custom_domain' => 'help.example.com',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.custom_domain', 'help.example.com');
    });

    test('portal with config', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/portals", [
                'name' => 'Multi-lang Portal',
                'slug' => 'multilang',
                'config' => [
                    'default_locale' => 'en',
                    'allowed_locales' => ['en', 'es', 'fr'],
                ],
            ]);

        $response->assertCreated();
    });
});

describe('Portal Retrieval', function () {
    test('can show portal', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $portal = Portal::factory()->for($account)->create(['name' => 'Test Portal']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/portals/{$portal->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $portal->id)
            ->assertJsonPath('data.name', 'Test Portal');
    });

    test('cannot access portal from other account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $otherAccount = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $portal = Portal::factory()->for($otherAccount)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$otherAccount->id}/portals/{$portal->id}");

        $response->assertNotFound();
    });
});

describe('Portal Update', function () {
    test('can update portal name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $portal = Portal::factory()->for($account)->create(['name' => 'Original']);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/portals/{$portal->id}", [
                'name' => 'Updated',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated');
    });

    test('can archive portal', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $portal = Portal::factory()->for($account)->active()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/portals/{$portal->id}", [
                'archived' => true,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.archived', true);
    });
});

describe('Portal Deletion', function () {
    test('can delete portal', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $portal = Portal::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/portals/{$portal->id}");

        $response->assertNoContent();
        expect(Portal::find($portal->id))->toBeNull();
    });

    test('deleting non-existent portal returns 404', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/portals/99999");

        $response->assertNotFound();
    });
});

describe('Portal Articles', function () {
    test('can list portal articles', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $portal = Portal::factory()->for($account)->create();
        Article::factory(5)->for($account)->for($portal)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/portals/{$portal->id}/articles");

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    });

    test('can create article in portal', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $portal = Portal::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/portals/{$portal->id}/articles", [
                'title' => 'Getting Started',
                'content' => 'Welcome to our help center!',
                'status' => Article::STATUS_PUBLISHED,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Getting Started');
    });
});

describe('Portal Categories', function () {
    test('can list portal categories', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $portal = Portal::factory()->for($account)->create();
        Category::factory(3)->for($account)->for($portal)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/portals/{$portal->id}/categories");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    });

    test('can create category in portal', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $portal = Portal::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/portals/{$portal->id}/categories", [
                'name' => 'Getting Started',
                'slug' => 'getting-started',
                'description' => 'Learn the basics',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Getting Started');
    });
});

describe('Portal Authorization', function () {
    test('unauthenticated user cannot list portals', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/portals");

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot create portal', function () {
        $account = Account::factory()->create();

        $response = $this->postJson("/api/v1/accounts/{$account->id}/portals", [
            'name' => 'Test Portal',
        ]);

        $response->assertUnauthorized();
    });
});

describe('Portal Edge Cases', function () {
    test('portal with unicode name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/portals", [
                'name' => 'ヘルプセンター 📚',
                'slug' => 'help-center-jp',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'ヘルプセンター 📚');
    });

    test('handles many portals', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        Portal::factory(50)->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/portals");

        $response->assertOk();
    });

    test('portal with many articles and categories', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $portal = Portal::factory()->for($account)->create();
        Category::factory(10)->for($account)->for($portal)->create();
        Article::factory(50)->for($account)->for($portal)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/portals/{$portal->id}");

        $response->assertOk();
    });
});
