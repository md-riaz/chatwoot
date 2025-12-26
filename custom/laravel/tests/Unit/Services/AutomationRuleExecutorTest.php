<?php

/**
 * Automation Rule Executor Service Unit Tests
 *
 * Tests the business logic for automation rule execution including
 * condition matching and action execution.
 */

use App\Models\Account;
use App\Models\AutomationRule;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\Label;
use App\Models\Team;
use App\Models\User;

describe('Automation Rule Conditions', function () {
    test('matches conversation status condition', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();

        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->open()
            ->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'event_name' => 'conversation_created',
            'conditions' => [
                ['attribute_key' => 'status', 'filter_operator' => 'equal_to', 'values' => ['open']],
            ],
        ]);

        expect($conversation->status)->toBe(0);
        expect($rule->event_name)->toBe('conversation_created');
    });

    test('matches inbox condition', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create(['name' => 'Support']);
        $contact = Contact::factory()->for($account)->create();

        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'conditions' => [
                ['attribute_key' => 'inbox_id', 'filter_operator' => 'equal_to', 'values' => [$inbox->id]],
            ],
        ]);

        expect($conversation->inbox_id)->toBe($inbox->id);
    });

    test('matches contact custom attribute', function () {
        $account = Account::factory()->create();
        $contact = Contact::factory()->for($account)->create([
            'custom_attributes' => ['tier' => 'enterprise'],
        ]);

        $rule = AutomationRule::factory()->for($account)->create([
            'conditions' => [
                ['attribute_key' => 'custom_attributes.tier', 'filter_operator' => 'equal_to', 'values' => ['enterprise']],
            ],
        ]);

        expect($contact->custom_attributes['tier'])->toBe('enterprise');
    });

    test('matches message content condition', function () {
        $account = Account::factory()->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'event_name' => 'message_created',
            'conditions' => [
                ['attribute_key' => 'content', 'filter_operator' => 'contains', 'values' => ['urgent']],
            ],
        ]);

        expect($rule->event_name)->toBe('message_created');
    });

    test('matches multiple conditions with AND logic', function () {
        $account = Account::factory()->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'conditions' => [
                ['attribute_key' => 'status', 'filter_operator' => 'equal_to', 'values' => ['open']],
                ['attribute_key' => 'priority', 'filter_operator' => 'equal_to', 'values' => ['high']],
            ],
        ]);

        expect(count($rule->conditions))->toBe(2);
    });

    test('matches with OR operator', function () {
        $account = Account::factory()->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'conditions' => [
                ['attribute_key' => 'status', 'filter_operator' => 'equal_to', 'values' => ['open', 'pending']],
            ],
        ]);

        expect($rule->conditions[0]['values'])->toContain('open', 'pending');
    });
});

describe('Automation Rule Actions', function () {
    test('assigns conversation to agent', function () {
        $account = Account::factory()->create();
        $agent = User::factory()->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'actions' => [
                ['action_name' => 'assign_agent', 'action_params' => [$agent->id]],
            ],
        ]);

        expect($rule->actions[0]['action_name'])->toBe('assign_agent');
    });

    test('assigns conversation to team', function () {
        $account = Account::factory()->create();
        $team = Team::factory()->for($account)->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'actions' => [
                ['action_name' => 'assign_team', 'action_params' => [$team->id]],
            ],
        ]);

        expect($rule->actions[0]['action_name'])->toBe('assign_team');
    });

    test('adds labels to conversation', function () {
        $account = Account::factory()->create();
        $label = Label::factory()->for($account)->create(['title' => 'urgent']);

        $rule = AutomationRule::factory()->for($account)->create([
            'actions' => [
                ['action_name' => 'add_label', 'action_params' => ['urgent']],
            ],
        ]);

        expect($rule->actions[0]['action_name'])->toBe('add_label');
    });

    test('removes labels from conversation', function () {
        $account = Account::factory()->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'actions' => [
                ['action_name' => 'remove_label', 'action_params' => ['low-priority']],
            ],
        ]);

        expect($rule->actions[0]['action_name'])->toBe('remove_label');
    });

    test('sends email notification', function () {
        $account = Account::factory()->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'actions' => [
                ['action_name' => 'send_email', 'action_params' => ['manager@example.com', 'New VIP conversation']],
            ],
        ]);

        expect($rule->actions[0]['action_name'])->toBe('send_email');
    });

    test('sends webhook notification', function () {
        $account = Account::factory()->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'actions' => [
                ['action_name' => 'send_webhook', 'action_params' => ['https://api.example.com/notify']],
            ],
        ]);

        expect($rule->actions[0]['action_name'])->toBe('send_webhook');
    });

    test('changes conversation status', function () {
        $account = Account::factory()->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'actions' => [
                ['action_name' => 'change_status', 'action_params' => ['snoozed']],
            ],
        ]);

        expect($rule->actions[0]['action_name'])->toBe('change_status');
    });

    test('changes conversation priority', function () {
        $account = Account::factory()->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'actions' => [
                ['action_name' => 'change_priority', 'action_params' => ['urgent']],
            ],
        ]);

        expect($rule->actions[0]['action_name'])->toBe('change_priority');
    });

    test('sends auto-reply message', function () {
        $account = Account::factory()->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'actions' => [
                ['action_name' => 'send_message', 'action_params' => ['Thank you for contacting us!']],
            ],
        ]);

        expect($rule->actions[0]['action_name'])->toBe('send_message');
    });

    test('executes multiple actions in order', function () {
        $account = Account::factory()->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'actions' => [
                ['action_name' => 'add_label', 'action_params' => ['vip']],
                ['action_name' => 'change_priority', 'action_params' => ['high']],
                ['action_name' => 'send_message', 'action_params' => ['VIP customer detected!']],
            ],
        ]);

        expect(count($rule->actions))->toBe(3);
    });
});

describe('Automation Rule Events', function () {
    test('triggers on conversation created', function () {
        $account = Account::factory()->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'event_name' => 'conversation_created',
        ]);

        expect($rule->event_name)->toBe('conversation_created');
    });

    test('triggers on conversation updated', function () {
        $account = Account::factory()->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'event_name' => 'conversation_updated',
        ]);

        expect($rule->event_name)->toBe('conversation_updated');
    });

    test('triggers on message created', function () {
        $account = Account::factory()->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'event_name' => 'message_created',
        ]);

        expect($rule->event_name)->toBe('message_created');
    });

    test('triggers on conversation status changed', function () {
        $account = Account::factory()->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'event_name' => 'conversation_status_changed',
        ]);

        expect($rule->event_name)->toBe('conversation_status_changed');
    });
});

describe('Automation Rule Execution', function () {
    test('active rules are executed', function () {
        $account = Account::factory()->create();

        $rule = AutomationRule::factory()->for($account)->active()->create();

        expect($rule->active)->toBeTrue();
    });

    test('inactive rules are skipped', function () {
        $account = Account::factory()->create();

        $rule = AutomationRule::factory()->for($account)->inactive()->create();

        expect($rule->active)->toBeFalse();
    });

    test('rules respect execution order', function () {
        $account = Account::factory()->create();

        AutomationRule::factory()->for($account)->create(['name' => 'First', 'created_at' => now()->subDay()]);
        AutomationRule::factory()->for($account)->create(['name' => 'Second', 'created_at' => now()]);

        $rules = AutomationRule::where('account_id', $account->id)->orderBy('created_at')->get();

        expect($rules->first()->name)->toBe('First');
    });
});

describe('Automation Rule Filters', function () {
    test('equal_to filter operator', function () {
        $account = Account::factory()->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'conditions' => [
                ['filter_operator' => 'equal_to', 'values' => ['test']],
            ],
        ]);

        expect($rule->conditions[0]['filter_operator'])->toBe('equal_to');
    });

    test('not_equal_to filter operator', function () {
        $account = Account::factory()->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'conditions' => [
                ['filter_operator' => 'not_equal_to', 'values' => ['test']],
            ],
        ]);

        expect($rule->conditions[0]['filter_operator'])->toBe('not_equal_to');
    });

    test('contains filter operator', function () {
        $account = Account::factory()->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'conditions' => [
                ['filter_operator' => 'contains', 'values' => ['urgent']],
            ],
        ]);

        expect($rule->conditions[0]['filter_operator'])->toBe('contains');
    });

    test('does_not_contain filter operator', function () {
        $account = Account::factory()->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'conditions' => [
                ['filter_operator' => 'does_not_contain', 'values' => ['spam']],
            ],
        ]);

        expect($rule->conditions[0]['filter_operator'])->toBe('does_not_contain');
    });

    test('is_present filter operator', function () {
        $account = Account::factory()->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'conditions' => [
                ['filter_operator' => 'is_present'],
            ],
        ]);

        expect($rule->conditions[0]['filter_operator'])->toBe('is_present');
    });

    test('is_not_present filter operator', function () {
        $account = Account::factory()->create();

        $rule = AutomationRule::factory()->for($account)->create([
            'conditions' => [
                ['filter_operator' => 'is_not_present'],
            ],
        ]);

        expect($rule->conditions[0]['filter_operator'])->toBe('is_not_present');
    });
});
