<?php

/**
 * Comprehensive Third-Party Integrations API Tests
 *
 * Tests third-party integrations including Slack, Linear, Dialogflow, etc.
 */

use App\Models\Account;
use App\Models\User;

describe('Slack Integration', function () {
    test('can list available integrations', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/integrations/apps");

        $response->assertOk();
    });

    test('can initiate slack oauth', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/integrations/slack/authorize");

        $response->assertOk();
    });

    test('can create slack integration', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/integrations/apps", [
                'app_id' => 'slack',
                'settings' => [
                    'channel' => '#support',
                ],
            ]);

        $response->assertCreated();
    });

    test('can update slack settings', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        // First create
        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/integrations/apps", [
                'app_id' => 'slack',
                'settings' => ['channel' => '#support'],
            ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/integrations/apps/slack", [
                'settings' => ['channel' => '#vip-support'],
            ]);

        $response->assertOk();
    });

    test('can delete slack integration', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/integrations/apps", [
                'app_id' => 'slack',
            ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/accounts/{$account->id}/integrations/apps/slack");

        $response->assertNoContent();
    });

    test('can list slack channels', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/integrations/slack/channels");

        $response->assertOk();
    });
});

describe('Linear Integration', function () {
    test('can create linear integration', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/integrations/apps", [
                'app_id' => 'linear',
                'settings' => [
                    'api_key' => 'mock_api_key',
                ],
            ]);

        $response->assertCreated();
    });

    test('can list linear teams', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/integrations/linear/teams");

        $response->assertOk();
    });

    test('can create linear issue from conversation', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/integrations/linear/issues", [
                'conversation_id' => 1,
                'title' => 'Customer Issue',
                'description' => 'Issue description',
                'team_id' => 'team_123',
            ]);

        $response->assertCreated();
    });
});

describe('Dialogflow Integration', function () {
    test('can create dialogflow integration', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/integrations/apps", [
                'app_id' => 'dialogflow',
                'settings' => [
                    'project_id' => 'my-dialogflow-project',
                    'credentials' => '{}',
                ],
            ]);

        $response->assertCreated();
    });

    test('can configure dialogflow inbox', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/integrations/dialogflow/inboxes", [
                'inbox_id' => 1,
            ]);

        $response->assertOk();
    });
});

describe('OpenAI Integration', function () {
    test('can create openai integration', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/integrations/apps", [
                'app_id' => 'openai',
                'settings' => [
                    'api_key' => 'sk-mock-key',
                ],
            ]);

        $response->assertCreated();
    });

    test('can get reply suggestions', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/integrations/openai/suggest", [
                'conversation_id' => 1,
            ]);

        $response->assertOk();
    });

    test('can summarize conversation', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/integrations/openai/summarize", [
                'conversation_id' => 1,
            ]);

        $response->assertOk();
    });
});

describe('Dyte Integration', function () {
    test('can create dyte integration', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/integrations/apps", [
                'app_id' => 'dyte',
                'settings' => [
                    'organization_id' => 'org_123',
                    'api_key' => 'mock_key',
                ],
            ]);

        $response->assertCreated();
    });

    test('can create meeting', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/integrations/dyte/meetings", [
                'conversation_id' => 1,
            ]);

        $response->assertCreated();
    });
});

describe('Webhooks Integration', function () {
    test('can create webhook', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/webhooks", [
                'url' => 'https://example.com/webhook',
                'subscriptions' => ['conversation_created', 'message_created'],
            ]);

        $response->assertCreated();
    });

    test('can test webhook', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/webhooks", [
                'url' => 'https://example.com/webhook',
                'subscriptions' => ['conversation_created'],
            ]);

        $webhookId = $createResponse->json('data.id');

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/webhooks/{$webhookId}/test");

        $response->assertOk();
    });

    test('can list webhook subscriptions', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/webhooks/subscriptions");

        $response->assertOk();
    });
});

describe('Captain AI Integration', function () {
    test('can enable captain for inbox', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/integrations/captain", [
                'inbox_id' => 1,
                'enabled' => true,
            ]);

        $response->assertOk();
    });

    test('can configure captain settings', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/integrations/captain", [
                'response_delay' => 30,
                'handoff_trigger' => 'always',
            ]);

        $response->assertOk();
    });
});

describe('Integration Authorization', function () {
    test('unauthenticated user cannot access integrations', function () {
        $account = Account::factory()->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/integrations/apps");

        $response->assertUnauthorized();
    });

    test('agent cannot create integrations', function () {
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($agent->id, ['role' =>  0]);

        $response = $this->actingAs($agent, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/integrations/apps", [
                'app_id' => 'slack',
            ]);

        $response->assertForbidden();
    });

    test('agent can view integrations', function () {
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($agent->id, ['role' =>  0]);

        $response = $this->actingAs($agent, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/integrations/apps");

        $response->assertOk();
    });
});

describe('Integration Edge Cases', function () {
    test('handles invalid integration app id', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/integrations/apps", [
                'app_id' => 'invalid_app',
            ]);

        $response->assertUnprocessable();
    });

    test('handles duplicate integration', function () {
        $admin = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($admin->id, ['role' =>   0]);

        // First create
        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/integrations/apps", [
                'app_id' => 'slack',
            ])->assertCreated();

        // Duplicate
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/accounts/{$account->id}/integrations/apps", [
                'app_id' => 'slack',
            ]);

        $response->assertUnprocessable();
    });
});
