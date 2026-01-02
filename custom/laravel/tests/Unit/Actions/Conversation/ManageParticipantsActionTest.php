<?php

/**
 * ManageParticipantsAction Unit Tests
 *
 * Tests the Action pattern implementation for participant management.
 */

use App\Actions\Conversation\ManageParticipantsAction;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Inbox;
use App\Models\User;
use App\Repositories\Conversation\ParticipantRepository;
use Illuminate\Validation\ValidationException;

describe('ManageParticipantsAction', function () {
    beforeEach(function () {
        $this->account = Account::factory()->create();
        $this->inbox = Inbox::factory()->for($this->account)->create();
        $this->contact = Contact::factory()->for($this->account)->create();
        $this->conversation = Conversation::factory()->for($this->account)->for($this->inbox)->for($this->contact)->create();
        
        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();
        $this->user3 = User::factory()->create();
        
        // Give users access to the account
        $this->account->users()->attach($this->user1->id, ['role' => 0]);
        $this->account->users()->attach($this->user2->id, ['role' => 0]);
        $this->account->users()->attach($this->user3->id, ['role' => 0]);

        $this->action = new ManageParticipantsAction();
    });

    test('can get participants for conversation', function () {
        ConversationParticipant::factory()->for($this->conversation)->for($this->user1)->for($this->account)->create();
        ConversationParticipant::factory()->for($this->conversation)->for($this->user2)->for($this->account)->create();

        $participants = $this->action->getParticipants($this->conversation);

        expect($participants)->toHaveCount(2);
        expect($participants->pluck('user_id')->toArray())->toContain($this->user1->id, $this->user2->id);
    });

    test('can add participants to conversation', function () {
        $participants = $this->action->addParticipants($this->conversation, [$this->user1->id, $this->user2->id]);

        expect($participants)->toHaveCount(2);
        
        $this->assertDatabaseHas('conversation_participants', [
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user1->id,
        ]);
        
        $this->assertDatabaseHas('conversation_participants', [
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user2->id,
        ]);
    });

    test('prevents duplicate participants when adding', function () {
        // Add participant first time
        ConversationParticipant::factory()->for($this->conversation)->for($this->user1)->for($this->account)->create();

        // Add same participant again
        $participants = $this->action->addParticipants($this->conversation, [$this->user1->id]);

        expect($participants)->toHaveCount(1);
        
        // Verify only one record exists
        $count = ConversationParticipant::where([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user1->id,
        ])->count();
        
        expect($count)->toBe(1);
    });

    test('can update participants for conversation', function () {
        // Add initial participants
        ConversationParticipant::factory()->for($this->conversation)->for($this->user1)->for($this->account)->create();
        ConversationParticipant::factory()->for($this->conversation)->for($this->user2)->for($this->account)->create();

        // Update to different set of participants
        $participants = $this->action->updateParticipants($this->conversation, [$this->user2->id, $this->user3->id]);

        expect($participants)->toHaveCount(2);
        
        // Verify user1 was removed
        $this->assertDatabaseMissing('conversation_participants', [
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user1->id,
        ]);
        
        // Verify user2 remains
        $this->assertDatabaseHas('conversation_participants', [
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user2->id,
        ]);
        
        // Verify user3 was added
        $this->assertDatabaseHas('conversation_participants', [
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user3->id,
        ]);
    });

    test('can remove participants from conversation', function () {
        ConversationParticipant::factory()->for($this->conversation)->for($this->user1)->for($this->account)->create();
        ConversationParticipant::factory()->for($this->conversation)->for($this->user2)->for($this->account)->create();

        $this->action->removeParticipants($this->conversation, [$this->user1->id]);

        // Verify user1 was removed
        $this->assertDatabaseMissing('conversation_participants', [
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user1->id,
        ]);
        
        // Verify user2 remains
        $this->assertDatabaseHas('conversation_participants', [
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user2->id,
        ]);
    });

    test('validates participants have inbox access', function () {
        $userWithoutAccess = User::factory()->create();

        expect(function () use ($userWithoutAccess) {
            $this->action->addParticipants($this->conversation, [$userWithoutAccess->id]);
        })->toThrow(ValidationException::class);
    });

    test('allows participants with inbox access', function () {
        expect(function () {
            $this->action->addParticipants($this->conversation, [$this->user1->id, $this->user2->id]);
        })->not->toThrow(ValidationException::class);
    });

    test('action can be called statically', function () {
        $participants = ManageParticipantsAction::run()->addParticipants($this->conversation, [$this->user1->id]);

        expect($participants)->toHaveCount(1);
        expect($participants->first()->user_id)->toBe($this->user1->id);
    });
});