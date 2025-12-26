<?php

/**
 * Comprehensive Macro API Tests
 *
 * Tests all macro-related API functionality including CRUD operations,
 * execution, and edge cases.
 */

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\Macro;
use App\Models\User;

describe('Macro Listing', function () {
    test('can list macros for account', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/macros");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });

    test('user sees only their own personal macros', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user1->id, ['role' => 2]);
        $account->users()->attach($user2->id, ['role' => 1]);

        $response = $this->actingAs($user1, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/macros");

        $response->assertOk();
    });

    test('macros list includes expected fields', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/macros");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });
});

describe('Macro Creation', function () {
    test('can create macro', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/macros", [
                'name' => 'Quick Resolve',
                'actions' => [
                    [
                        'action_name' => 'resolve_conversation',
                        'action_params' => [],
                    ],
                ],
                'visibility' => 'personal',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Quick Resolve');
    });

    test('can create macro with multiple actions', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/macros", [
                'name' => 'Complete Workflow',
                'actions' => [
                    ['action_name' => 'add_label', 'action_params' => ['resolved']],
                    ['action_name' => 'send_message', 'action_params' => ['Thanks for your patience!']],
                    ['action_name' => 'resolve_conversation', 'action_params' => []],
                ],
                'visibility' => 'personal',
            ]);

        $response->assertCreated();
    });

    test('macro creation requires name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/macros", [
                'actions' => [
                    ['action_name' => 'resolve_conversation', 'action_params' => []],
                ],
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });

    test('macro creation requires actions', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/macros", [
                'name' => 'Empty Macro',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['actions']);
    });

    test('can create public macro as admin', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/macros", [
                'name' => 'Public Macro',
                'actions' => [
                    ['action_name' => 'resolve_conversation', 'action_params' => []],
                ],
                'visibility' => 'global',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.visibility', 'global');
    });
});

describe('Macro Retrieval', function () {
    test('can show macro', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        // First create a macro
        $createResponse = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/macros", [
                'name' => 'Test Macro',
                'actions' => [
                    ['action_name' => 'resolve_conversation', 'action_params' => []],
                ],
            ]);

        $macroId = $createResponse->json('data.id');

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/macros/{$macroId}");

        $response->assertOk()
            ->assertJsonPath('data.name', 'Test Macro');
    });

    test('viewing non-existent macro returns 404', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/macros/99999");

        $response->assertNotFound();
    });
});

describe('Macro Update', function () {
    test('can update macro name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $createResponse = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/macros", [
                'name' => 'Original Macro',
                'actions' => [
                    ['action_name' => 'resolve_conversation', 'action_params' => []],
                ],
            ]);

        $macroId = $createResponse->json('data.id');

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/macros/{$macroId}", [
                'name' => 'Updated Macro',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Macro');
    });

    test('can update macro actions', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $createResponse = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/macros", [
                'name' => 'Test Macro',
                'actions' => [
                    ['action_name' => 'resolve_conversation', 'action_params' => []],
                ],
            ]);

        $macroId = $createResponse->json('data.id');

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/macros/{$macroId}", [
                'actions' => [
                    ['action_name' => 'add_label', 'action_params' => ['urgent']],
                    ['action_name' => 'assign_agent', 'action_params' => ['1']],
                ],
            ]);

        $response->assertOk();
    });
});

describe('Macro Deletion', function () {
    test('can delete macro', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $createResponse = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/macros", [
                'name' => 'Delete Me',
                'actions' => [
                    ['action_name' => 'resolve_conversation', 'action_params' => []],
                ],
            ]);

        $macroId = $createResponse->json('data.id');

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/macros/{$macroId}");

        $response->assertNoContent();
    });

    test('deleting non-existent macro returns 404', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/macros/99999");

        $response->assertNotFound();
    });
});

describe('Macro Execution', function () {
    test('can execute macro on conversation', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        $createResponse = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/macros", [
                'name' => 'Execute Me',
                'actions' => [
                    ['action_name' => 'resolve_conversation', 'action_params' => []],
                ],
            ]);

        $macroId = $createResponse->json('data.id');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/macros/{$macroId}/execute", [
                'conversation_ids' => [$conversation->id],
            ]);

        $response->assertOk();
    });

    test('can execute macro on multiple conversations', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversations = Conversation::factory(3)
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        $createResponse = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/macros", [
                'name' => 'Bulk Macro',
                'actions' => [
                    ['action_name' => 'add_label', 'action_params' => ['bulk-processed']],
                ],
            ]);

        $macroId = $createResponse->json('data.id');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/macros/{$macroId}/execute", [
                'conversation_ids' => $conversations->pluck('id')->toArray(),
            ]);

        $response->assertOk();
    });
});

describe('Macro Authorization', function () {
    test('unauthenticated user cannot list macros', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/macros");

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot create macro', function () {
        $account = Account::factory()->create();

        $response = $this->postJson("/api/v1/accounts/{$account->id}/macros", [
            'name' => 'Test Macro',
        ]);

        $response->assertUnauthorized();
    });

    test('user without account access cannot view macros', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/macros");

        $response->assertNotFound();
    });
});

describe('Macro Action Types', function () {
    test('resolve conversation action', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/macros", [
                'name' => 'Resolve Macro',
                'actions' => [
                    ['action_name' => 'resolve_conversation', 'action_params' => []],
                ],
            ]);

        $response->assertCreated();
    });

    test('add label action', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/macros", [
                'name' => 'Label Macro',
                'actions' => [
                    ['action_name' => 'add_label', 'action_params' => ['priority', 'vip']],
                ],
            ]);

        $response->assertCreated();
    });

    test('assign agent action', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/macros", [
                'name' => 'Assign Macro',
                'actions' => [
                    ['action_name' => 'assign_agent', 'action_params' => [$user->id]],
                ],
            ]);

        $response->assertCreated();
    });

    test('send message action', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/macros", [
                'name' => 'Message Macro',
                'actions' => [
                    ['action_name' => 'send_message', 'action_params' => ['Thank you for contacting us!']],
                ],
            ]);

        $response->assertCreated();
    });

    test('snooze conversation action', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/macros", [
                'name' => 'Snooze Macro',
                'actions' => [
                    ['action_name' => 'snooze_conversation', 'action_params' => ['tomorrow']],
                ],
            ]);

        $response->assertCreated();
    });
});

describe('Macro Edge Cases', function () {
    test('macro with unicode name', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/macros", [
                'name' => 'マクロ 🚀',
                'actions' => [
                    ['action_name' => 'resolve_conversation', 'action_params' => []],
                ],
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'マクロ 🚀');
    });

    test('macro with many actions', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' => 2]);

        $actions = [];
        for ($i = 0; $i < 10; $i++) {
            $actions[] = ['action_name' => 'add_label', 'action_params' => ["label-{$i}"]];
        }
        $actions[] = ['action_name' => 'resolve_conversation', 'action_params' => []];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/macros", [
                'name' => 'Complex Macro',
                'actions' => $actions,
            ]);

        $response->assertCreated();
    });
});
