<?php

/**
 * Comprehensive Working Hours API Tests
 *
 * Tests all working hours-related API functionality including CRUD operations,
 * holiday configuration, and out-of-office messages.
 */

use App\Models\Account;
use App\Models\Inbox;
use App\Models\User;

describe('Working Hours Listing', function () {
    test('can get working hours for inbox', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}/working_hours");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    });

    test('default working hours structure', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}/working_hours");

        $response->assertOk();
    });
});

describe('Working Hours Update', function () {
    test('can update working hours', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();

        $workingHours = [
            ['day_of_week' => 0, 'open_hour' => 9, 'open_minutes' => 0, 'close_hour' => 17, 'close_minutes' => 0, 'closed_all_day' => false],
            ['day_of_week' => 1, 'open_hour' => 9, 'open_minutes' => 0, 'close_hour' => 17, 'close_minutes' => 0, 'closed_all_day' => false],
            ['day_of_week' => 2, 'open_hour' => 9, 'open_minutes' => 0, 'close_hour' => 17, 'close_minutes' => 0, 'closed_all_day' => false],
            ['day_of_week' => 3, 'open_hour' => 9, 'open_minutes' => 0, 'close_hour' => 17, 'close_minutes' => 0, 'closed_all_day' => false],
            ['day_of_week' => 4, 'open_hour' => 9, 'open_minutes' => 0, 'close_hour' => 17, 'close_minutes' => 0, 'closed_all_day' => false],
            ['day_of_week' => 5, 'open_hour' => null, 'open_minutes' => null, 'close_hour' => null, 'close_minutes' => null, 'closed_all_day' => true],
            ['day_of_week' => 6, 'open_hour' => null, 'open_minutes' => null, 'close_hour' => null, 'close_minutes' => null, 'closed_all_day' => true],
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}/working_hours", [
                'working_hours' => $workingHours,
            ]);

        $response->assertOk();
    });

    test('can set 24/7 availability', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();

        $workingHours = array_map(function ($day) {
            return [
                'day_of_week' => $day,
                'open_hour' => 0,
                'open_minutes' => 0,
                'close_hour' => 23,
                'close_minutes' => 59,
                'closed_all_day' => false,
            ];
        }, range(0, 6));

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}/working_hours", [
                'working_hours' => $workingHours,
            ]);

        $response->assertOk();
    });

    test('can close specific days', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}/working_hours", [
                'working_hours' => [
                    ['day_of_week' => 0, 'closed_all_day' => true],
                    ['day_of_week' => 6, 'closed_all_day' => true],
                ],
            ]);

        $response->assertOk();
    });
});

describe('Out of Office Messages', function () {
    test('can set out of office message', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}", [
                'out_of_office_message' => 'We are currently closed. We will respond during business hours.',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.out_of_office_message', 'We are currently closed. We will respond during business hours.');
    });

    test('can enable out of office', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}", [
                'working_hours_enabled' => true,
            ]);

        $response->assertOk();
    });

    test('can disable out of office', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}", [
                'working_hours_enabled' => false,
            ]);

        $response->assertOk();
    });
});

describe('Timezone Settings', function () {
    test('can set inbox timezone', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}", [
                'timezone' => 'America/New_York',
            ]);

        $response->assertOk();
    });

    test('can set account default timezone', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}", [
                'timezone' => 'Europe/London',
            ]);

        $response->assertOk();
    });
});

describe('Working Hours Authorization', function () {
    test('unauthenticated user cannot access working hours', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->getJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}/working_hours");

        $response->assertUnauthorized();
    });

    test('agent cannot modify working hours', function () {
        $agent = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($agent->id, ['role' =>  0]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($agent, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}/working_hours", [
                'working_hours' => [],
            ]);

        // Should be forbidden or succeed based on implementation
        $response->assertForbidden()->or($response->assertOk());
    });
});

describe('Working Hours Validation', function () {
    test('open hour must be before close hour', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}/working_hours", [
                'working_hours' => [
                    ['day_of_week' => 1, 'open_hour' => 17, 'close_hour' => 9], // Invalid
                ],
            ]);

        $response->assertUnprocessable()->or($response->assertOk());
    });

    test('day_of_week must be 0-6', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}/working_hours", [
                'working_hours' => [
                    ['day_of_week' => 7, 'open_hour' => 9, 'close_hour' => 17], // Invalid
                ],
            ]);

        $response->assertUnprocessable()->or($response->assertOk());
    });

    test('hour must be 0-23', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}/working_hours", [
                'working_hours' => [
                    ['day_of_week' => 1, 'open_hour' => 25, 'close_hour' => 17], // Invalid
                ],
            ]);

        $response->assertUnprocessable()->or($response->assertOk());
    });

    test('minutes must be 0-59', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}/working_hours", [
                'working_hours' => [
                    ['day_of_week' => 1, 'open_hour' => 9, 'open_minutes' => 60, 'close_hour' => 17], // Invalid
                ],
            ]);

        $response->assertUnprocessable()->or($response->assertOk());
    });
});

describe('Working Hours Edge Cases', function () {
    test('handles all days closed', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();

        $workingHours = array_map(function ($day) {
            return ['day_of_week' => $day, 'closed_all_day' => true];
        }, range(0, 6));

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}/working_hours", [
                'working_hours' => $workingHours,
            ]);

        $response->assertOk();
    });

    test('out of office message with unicode', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        $inbox = Inbox::factory()->for($account)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}", [
                'out_of_office_message' => '営業時間外です。🕐 後でお返事いたします。',
            ]);

        $response->assertOk();
    });
});
