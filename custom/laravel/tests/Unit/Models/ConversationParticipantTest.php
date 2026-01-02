<?php

/**
 * ConversationParticipant Model Unit Tests
 *
 * Tests model validations, relationships, and business logic.
 */

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Inbox;
use App\Models\User;
use Illuminate\Validation\ValidationException;

describe('ConversationParticipant Model', function () {
    test('belongs to account', function () {
        $participant = ConversationParticipant::factory()->create();
        
        expect($participant->account)->toBeInstanceOf(Account::class);
    });

    test('belongs to conversation', function () {
        $participant = ConversationParticipant::factory()->create();
        
        expect($participant->conversation)->toBeInstanceOf(Conversation::class);
    });

    test('belongs to user', function () {
        $participant = ConversationParticipant::factory()->create();
        
        expect($participant->user)->toBeInstanceOf(User::class);
    });

    test('automatically sets account_id from conversation', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        $participant = new ConversationParticipant([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
        ]);

        $participant->save();

        expect($participant->account_id)->toBe($account->id);
    });

    test('validates required fields', function () {
        expect(function () {
            ConversationParticipant::create([]);
        })->toThrow(ValidationException::class);
    });

    test('validates uniqueness of user per conversation', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]);

        // Create first participant
        ConversationParticipant::create([
            'account_id' => $account->id,
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
        ]);

        // Try to create duplicate
        expect(function () use ($account, $conversation, $user) {
            ConversationParticipant::create([
                'account_id' => $account->id,
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
            ]);
        })->toThrow(ValidationException::class);
    });

    test('validates user has inbox access', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();
        $user = User::factory()->create(); // Not attached to account

        expect(function () use ($account, $conversation, $user) {
            ConversationParticipant::create([
                'account_id' => $account->id,
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
            ]);
        })->toThrow(ValidationException::class);
    });

    test('allows user with inbox access', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()->for($account)->for($inbox)->for($contact)->create();
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => 0]); // Give access

        $participant = ConversationParticipant::create([
            'account_id' => $account->id,
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
        ]);

        expect($participant)->toBeInstanceOf(ConversationParticipant::class);
        expect($participant->user_id)->toBe($user->id);
        expect($participant->conversation_id)->toBe($conversation->id);
        expect($participant->account_id)->toBe($account->id);
    });
});