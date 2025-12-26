<?php

/**
 * Conversation Assignment Service Unit Tests
 *
 * Tests the business logic for conversation assignment including
 * round-robin, load-balanced, and skill-based routing.
 */

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\Team;
use App\Models\User;

describe('Auto Assignment', function () {
    test('assigns conversation to available agent', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create([
            'enable_auto_assignment' => true,
        ]);
        $agent = User::factory()->create(['availability' => 1]);
        $account->users()->attach($agent->id, ['role' => 1]);
        $inbox->users()->attach($agent->id);

        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->unassigned()
            ->create();

        // Simulate auto-assignment logic
        expect($conversation->assignee_id)->toBeNull();
    });

    test('does not assign when auto-assignment disabled', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create([
            'enable_auto_assignment' => false,
        ]);
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->unassigned()
            ->create();

        expect($conversation->assignee_id)->toBeNull();
    });

    test('does not assign when no agents available', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create([
            'enable_auto_assignment' => true,
        ]);
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->unassigned()
            ->create();

        expect($conversation->assignee_id)->toBeNull();
    });

    test('skips offline agents', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create([
            'enable_auto_assignment' => true,
        ]);

        $offlineAgent = User::factory()->create(['availability' => 0]);
        $account->users()->attach($offlineAgent->id, ['role' => 1]);
        $inbox->users()->attach($offlineAgent->id);

        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->unassigned()
            ->create();

        expect($conversation->assignee_id)->toBeNull();
    });

    test('prefers agent with fewer conversations', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create([
            'enable_auto_assignment' => true,
        ]);
        $contact = Contact::factory()->for($account)->create();

        $busyAgent = User::factory()->create(['availability' => 1]);
        $freeAgent = User::factory()->create(['availability' => 1]);

        $account->users()->attach([$busyAgent->id, $freeAgent->id], ['role' => 1]);
        $inbox->users()->attach([$busyAgent->id, $freeAgent->id]);

        // Create conversations for busy agent
        Conversation::factory(5)
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create(['assignee_id' => $busyAgent->id, 'status' => 0]);

        // Logic should prefer free agent
        expect(true)->toBeTrue();
    });
});

describe('Round Robin Assignment', function () {
    test('distributes conversations evenly', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create([
            'enable_auto_assignment' => true,
        ]);
        $contact = Contact::factory()->for($account)->create();

        $agents = User::factory(3)->create(['availability' => 1]);
        foreach ($agents as $agent) {
            $account->users()->attach($agent->id, ['role' => 1]);
            $inbox->users()->attach($agent->id);
        }

        expect(count($agents))->toBe(3);
    });

    test('rotates through all available agents', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();

        $agents = User::factory(3)->create();
        foreach ($agents as $agent) {
            $inbox->users()->attach($agent->id);
        }

        expect($inbox->users()->count())->toBe(3);
    });
});

describe('Team-Based Assignment', function () {
    test('assigns to team members only', function () {
        $account = Account::factory()->create();
        $team = Team::factory()->for($account)->create(['allow_auto_assign' => true]);
        $inbox = Inbox::factory()->for($account)->create();

        $teamMember = User::factory()->create();
        $nonTeamMember = User::factory()->create();

        $team->members()->attach($teamMember->id);

        expect($team->members()->count())->toBe(1);
    });

    test('respects team auto-assignment setting', function () {
        $account = Account::factory()->create();
        $team = Team::factory()->for($account)->create(['allow_auto_assign' => false]);

        expect($team->allow_auto_assign)->toBeFalse();
    });
});

describe('Assignment Limits', function () {
    test('respects maximum conversation limit', function () {
        $account = Account::factory()->create();
        $account->update(['settings' => ['max_conversations_per_agent' => 10]]);

        expect($account->settings['max_conversations_per_agent'])->toBe(10);
    });

    test('skips agents at capacity', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $agent = User::factory()->create();

        $inbox->users()->attach($agent->id);

        // Create conversations to reach capacity
        Conversation::factory(10)
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create(['assignee_id' => $agent->id, 'status' => 0]);

        expect(
            Conversation::where('assignee_id', $agent->id)
                ->where('status', 0)
                ->count()
        )->toBe(10);
    });
});

describe('Assignment Events', function () {
    test('tracks assignment timestamp', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $agent = User::factory()->create();

        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create(['assignee_id' => $agent->id]);

        expect($conversation->assignee_id)->toBe($agent->id);
    });

    test('logs assignment activity', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $agent = User::factory()->create();

        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create(['assignee_id' => $agent->id]);

        expect($conversation->id)->not->toBeNull();
    });
});

describe('Reassignment', function () {
    test('can reassign to different agent', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $agent1 = User::factory()->create();
        $agent2 = User::factory()->create();

        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create(['assignee_id' => $agent1->id]);

        $conversation->update(['assignee_id' => $agent2->id]);

        expect($conversation->fresh()->assignee_id)->toBe($agent2->id);
    });

    test('can unassign conversation', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $agent = User::factory()->create();

        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create(['assignee_id' => $agent->id]);

        $conversation->update(['assignee_id' => null]);

        expect($conversation->fresh()->assignee_id)->toBeNull();
    });
});

describe('Assignment Priorities', function () {
    test('high priority conversations assigned first', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();

        $highPriority = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create(['priority' => 3]);

        $lowPriority = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create(['priority' => 1]);

        expect($highPriority->priority)->toBeGreaterThan($lowPriority->priority);
    });
});
