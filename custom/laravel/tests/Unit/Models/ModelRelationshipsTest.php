<?php

/**
 * Comprehensive Model Unit Tests
 *
 * Tests all model relationships, scopes, accessors, and mutators.
 */

use App\Models\Account;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\Label;
use App\Models\Message;
use App\Models\Team;
use App\Models\User;

describe('Account Model', function () {
    test('account has many users', function () {
        $account = Account::factory()->create();
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' =>   0]);

        expect($account->users)->toHaveCount(1);
        expect($account->users->first()->id)->toBe($user->id);
    });

    test('account has many inboxes', function () {
        $account = Account::factory()->create();
        Inbox::factory(3)->for($account)->create();

        expect($account->inboxes)->toHaveCount(3);
    });

    test('account has many contacts', function () {
        $account = Account::factory()->create();
        Contact::factory(5)->for($account)->create();

        expect($account->contacts)->toHaveCount(5);
    });

    test('account has many conversations', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        Conversation::factory(3)->for($account)->for($inbox)->for($contact)->create();

        expect($account->conversations)->toHaveCount(3);
    });

    test('account has many teams', function () {
        $account = Account::factory()->create();
        Team::factory(2)->for($account)->create();

        expect($account->teams)->toHaveCount(2);
    });

    test('account has many labels', function () {
        $account = Account::factory()->create();
        Label::factory(4)->for($account)->create();

        expect($account->labels)->toHaveCount(4);
    });
});

describe('Conversation Model', function () {
    test('conversation belongs to account', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        expect($conversation->account->id)->toBe($account->id);
    });

    test('conversation belongs to inbox', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        expect($conversation->inbox->id)->toBe($inbox->id);
    });

    test('conversation belongs to contact', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        expect($conversation->contact->id)->toBe($contact->id);
    });

    test('conversation has many messages', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        Message::factory(5)->for($account)->for($conversation)->for($inbox)->create();

        expect($conversation->messages)->toHaveCount(5);
    });

    test('conversation can have assignee', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create(['assignee_id' => $user->id]);

        expect($conversation->assignee->id)->toBe($user->id);
    });

    test('conversation open scope', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();

        Conversation::factory(3)->for($account)->for($inbox)->for($contact)->open()->create();
        Conversation::factory(2)->for($account)->for($inbox)->for($contact)->resolved()->create();

        expect(Conversation::open()->count())->toBe(3);
    });

    test('conversation unassigned scope', function () {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();

        Conversation::factory(2)->for($account)->for($inbox)->for($contact)->unassigned()->create();
        Conversation::factory(3)->for($account)->for($inbox)->for($contact)->assigned()->create();

        expect(Conversation::unassigned()->count())->toBe(2);
    });

    test('conversation status constants', function () {
        expect(Conversation::STATUS_OPEN)->toBe(0);
        expect(Conversation::STATUS_RESOLVED)->toBe(1);
        expect(Conversation::STATUS_PENDING)->toBe(2);
        expect(Conversation::STATUS_SNOOZED)->toBe(3);
    });

    test('conversation priority constants', function () {
        expect(Conversation::PRIORITY_NONE)->toBe(0);
        expect(Conversation::PRIORITY_LOW)->toBe(1);
        expect(Conversation::PRIORITY_MEDIUM)->toBe(2);
        expect(Conversation::PRIORITY_HIGH)->toBe(3);
        expect(Conversation::PRIORITY_URGENT)->toBe(4);
    });

    test('conversation generates uuid on creation', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        expect($conversation->uuid)->not->toBeNull();
        expect(strlen($conversation->uuid))->toBe(36);
    });
});

describe('Message Model', function () {
    test('message belongs to conversation', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();
        $message = Message::factory()
            ->for($account)
            ->for($conversation)
            ->for($inbox)
            ->create();

        expect($message->conversation->id)->toBe($conversation->id);
    });

    test('message type constants', function () {
        expect(Message::TYPE_INCOMING)->toBe(0);
        expect(Message::TYPE_OUTGOING)->toBe(1);
        expect(Message::TYPE_ACTIVITY)->toBe(2);
    });

    test('message content type constants', function () {
        expect(Message::CONTENT_TEXT)->toBe(0);
    });

    test('message status constants', function () {
        expect(Message::STATUS_SENT)->toBe(0);
    });

    test('message can be incoming', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();
        $message = Message::factory()
            ->for($account)
            ->for($conversation)
            ->for($inbox)
            ->incoming()
            ->create();

        expect($message->message_type)->toBe(Message::TYPE_INCOMING);
    });

    test('message can be outgoing', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();
        $message = Message::factory()
            ->for($account)
            ->for($conversation)
            ->for($inbox)
            ->outgoing()
            ->create();

        expect($message->message_type)->toBe(Message::TYPE_OUTGOING);
    });

    test('message can be private note', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();
        $message = Message::factory()
            ->for($account)
            ->for($conversation)
            ->for($inbox)
            ->privateNote()
            ->create();

        expect($message->private)->toBeTrue();
    });
});

describe('Contact Model', function () {
    test('contact belongs to account', function () {
        $account = Account::factory()->create();
        $contact = Contact::factory()->for($account)->create();

        expect($contact->account->id)->toBe($account->id);
    });

    test('contact has many conversations', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();

        Conversation::factory(3)->for($account)->for($inbox)->for($contact)->create();

        expect($contact->conversations)->toHaveCount(3);
    });

    test('contact custom_attributes is cast to array', function () {
        $account = Account::factory()->create();
        $contact = Contact::factory()->for($account)->create([
            'custom_attributes' => ['tier' => 'enterprise', 'plan' => 'premium'],
        ]);

        expect($contact->custom_attributes)->toBeArray();
        expect($contact->custom_attributes['tier'])->toBe('enterprise');
    });
});

describe('Inbox Model', function () {
    test('inbox belongs to account', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();

        expect($inbox->account->id)->toBe($account->id);
    });

    test('inbox has many conversations', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();

        Conversation::factory(4)->for($account)->for($inbox)->for($contact)->create();

        expect($inbox->conversations)->toHaveCount(4);
    });

    test('inbox has many users', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $inbox->users()->attach([$user1->id, $user2->id]);

        expect($inbox->users)->toHaveCount(2);
    });
});

describe('Team Model', function () {
    test('team belongs to account', function () {
        $account = Account::factory()->create();
        $team = Team::factory()->for($account)->create();

        expect($team->account->id)->toBe($account->id);
    });

    test('team has many members', function () {
        $account = Account::factory()->create();
        $team = Team::factory()->for($account)->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $team->members()->attach([$user1->id, $user2->id]);

        expect($team->members)->toHaveCount(2);
    });

    test('team allow_auto_assign is boolean', function () {
        $account = Account::factory()->create();
        $team = Team::factory()->for($account)->autoAssign()->create();

        expect($team->allow_auto_assign)->toBeTrue();
    });
});

describe('Label Model', function () {
    test('label belongs to account', function () {
        $account = Account::factory()->create();
        $label = Label::factory()->for($account)->create();

        expect($label->account->id)->toBe($account->id);
    });

    test('label show_on_sidebar is boolean', function () {
        $account = Account::factory()->create();
        $label = Label::factory()->for($account)->showOnSidebar()->create();

        expect($label->show_on_sidebar)->toBeTrue();
    });
});

describe('Campaign Model', function () {
    test('campaign type constants', function () {
        expect(Campaign::TYPE_ONGOING)->toBe(0);
        expect(Campaign::TYPE_ONE_OFF)->toBe(1);
    });

    test('campaign status constants', function () {
        expect(Campaign::STATUS_ACTIVE)->toBe(0);
        expect(Campaign::STATUS_COMPLETED)->toBe(1);
    });

    test('campaign isOngoing method', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $campaign = Campaign::factory()->for($account)->for($inbox)->ongoing()->create();

        expect($campaign->isOngoing())->toBeTrue();
    });

    test('campaign isOneOff method', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $campaign = Campaign::factory()->for($account)->for($inbox)->oneOff()->create();

        expect($campaign->isOneOff())->toBeTrue();
    });
});
