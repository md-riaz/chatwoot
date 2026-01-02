<?php

/**
 * ParticipantService Unit Tests
 *
 * Tests participant management service functionality.
 */

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Inbox;
use App\Models\User;
use App\Services\ParticipantService;
use Illuminate\Validation\ValidationException;

describe('ParticipantService', function () {
    beforeEach(function () {
        $this->service = new ParticipantService();
        
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
    });

    test('can get participants for conversation', function () {
        ConversationParticipant::factory()->for($this->conversation)->for($this->user1)->for($this->account)->create();
        ConversationParticipant::factory()->for($this->conversation)->for($this->user2)->for($this->account)->create();

        $participants = $this->service->getParticipants($this->conversation);

        expect($participants)->toHaveCount(2);
        expect($participants->pluck('user_id')->toArray())->toContain($this->user1->id, $this->user2->id);
    });

    test('can add participants to conversation', function () {
        $participants = $this->service->addParticipants($this->conversation, [$this->user1->id, $this->user2->id]);

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
        $participants = $this->service->addParticipants($this->conversation, [$this->user1->id]);

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
        $participants = $this->service->updateParticipants($this->conversation, [$this->user2->id, $this->user3->id]);

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

        $this->service->removeParticipants($this->conversation, [$this->user1->id]);

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

    test('can get current participant IDs', function () {
        ConversationParticipant::factory()->for($this->conversation)->for($this->user1)->for($this->account)->create();
        ConversationParticipant::factory()->for($this->conversation)->for($this->user2)->for($this->account)->create();

        $participantIds = $this->service->getCurrentParticipantIds($this->conversation);

        expect($participantIds)->toHaveCount(2);
        expect($participantIds)->toContain($this->user1->id, $this->user2->id);
    });

    test('can check if user can participate', function () {
        // User with access
        $canParticipate = $this->service->canUserParticipate($this->conversation, $this->user1);
        expect($canParticipate)->toBeTrue();

        // User without access
        $userWithoutAccess = User::factory()->create();
        $canParticipate = $this->service->canUserParticipate($this->conversation, $userWithoutAccess);
        expect($canParticipate)->toBeFalse();
    });

    test('validates participants have inbox access', function () {
        $userWithoutAccess = User::factory()->create();

        expect(function () use ($userWithoutAccess) {
            $this->service->validateParticipants($this->conversation, [$userWithoutAccess->id]);
        })->toThrow(ValidationException::class);
    });

    test('allows participants with inbox access', function () {
        expect(function () {
            $this->service->validateParticipants($this->conversation, [$this->user1->id, $this->user2->id]);
        })->not->toThrow(ValidationException::class);
    });
});