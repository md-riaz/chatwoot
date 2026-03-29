<?php

use App\Models\Account;
use App\Models\Channels\Api;
use App\Models\Inbox;
use App\Models\User;

describe('Inbox members parity', function () {
    test('can fetch inbox with members count and list collaborators', function () {
        $account = Account::factory()->create();
        $admin = User::factory()->create();
        $agent = User::factory()->create();

        $account->users()->attach($admin->id, ['role' => 1]);
        $account->users()->attach($agent->id, ['role' => 0]);

        $channel = Api::factory()->create([
            'account_id' => $account->id,
        ]);

        $inbox = Inbox::create([
            'account_id' => $account->id,
            'name' => 'Support API',
            'channel_type' => 'Channel::Api',
            'channel_id' => $channel->id,
        ]);

        $inbox->members()->attach($agent->id);

        $showResponse = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}");

        $showResponse->assertOk();
        $showResponse->assertJsonPath('data.members_count', 1);

        $membersResponse = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/accounts/{$account->id}/inboxes/{$inbox->id}/members");

        $membersResponse->assertOk();
        $membersResponse->assertJsonCount(1);
    });
});
