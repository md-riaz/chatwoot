<?php

use App\Actions\Conversations\ManageDraftMessageAction;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

describe('ManageDraftMessageAction', function () {
    beforeEach(function () {
        Cache::flush();
        
        $this->user = User::factory()->create();
        $this->account = Account::factory()->create();
        $this->inbox = Inbox::factory()->for($this->account)->create();
        $this->contact = Contact::factory()->for($this->account)->create();
        $this->conversation = Conversation::factory()
            ->for($this->account)
            ->for($this->inbox)
            ->for($this->contact)
            ->create();
        
        $this->action = new ManageDraftMessageAction();
    });

    describe('getDraft', function () {
        test('returns null when no draft exists', function () {
            $result = $this->action->getDraft($this->conversation, $this->user->id);
            
            expect($result)->toBeNull();
        });

        test('returns draft data when draft exists', function () {
            $message = 'Test draft message';
            
            // Save a draft first
            $savedDraft = $this->action->saveDraft($this->conversation, $this->user->id, $message);
            
            // Retrieve the draft
            $result = $this->action->getDraft($this->conversation, $this->user->id);
            
            expect($result)->toBeArray()
                ->and($result['message'])->toBe($message)
                ->and($result['user_id'])->toBe($this->user->id)
                ->and($result['updated_at'])->toBeString();
        });
    });

    describe('saveDraft', function () {
        test('saves draft message successfully', function () {
            $message = 'Test draft message';
            
            $result = $this->action->saveDraft($this->conversation, $this->user->id, $message);
            
            expect($result)->toBeArray()
                ->and($result['message'])->toBe($message)
                ->and($result['user_id'])->toBe($this->user->id)
                ->and($result['updated_at'])->toBeString();
        });

        test('overwrites existing draft', function () {
            $originalMessage = 'Original message';
            $updatedMessage = 'Updated message';
            
            // Save original draft
            $this->action->saveDraft($this->conversation, $this->user->id, $originalMessage);
            
            // Update draft
            $result = $this->action->saveDraft($this->conversation, $this->user->id, $updatedMessage);
            
            expect($result['message'])->toBe($updatedMessage);
            
            // Verify the draft was updated
            $retrieved = $this->action->getDraft($this->conversation, $this->user->id);
            expect($retrieved['message'])->toBe($updatedMessage);
        });

        test('throws validation exception on conflict', function () {
            $message = 'Test message';
            
            // Save initial draft
            $initialDraft = $this->action->saveDraft($this->conversation, $this->user->id, $message);
            
            // Wait to ensure different timestamp
            sleep(1);
            
            // Update draft (simulating another session)
            $this->action->saveDraft($this->conversation, $this->user->id, 'Updated message');
            
            // Try to save with old timestamp (should fail)
            expect(fn() => $this->action->saveDraft(
                $this->conversation,
                $this->user->id,
                'Conflicting message',
                $initialDraft['updated_at']
            ))->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        test('allows save without conflict check when no timestamp provided', function () {
            $message = 'Test message';
            
            // Save initial draft
            $this->action->saveDraft($this->conversation, $this->user->id, $message);
            
            // Update without timestamp (should succeed)
            $result = $this->action->saveDraft($this->conversation, $this->user->id, 'Updated message');
            
            expect($result['message'])->toBe('Updated message');
        });
    });

    describe('deleteDraft', function () {
        test('deletes existing draft', function () {
            $message = 'Test draft message';
            
            // Save a draft first
            $this->action->saveDraft($this->conversation, $this->user->id, $message);
            
            // Verify draft exists
            $draft = $this->action->getDraft($this->conversation, $this->user->id);
            expect($draft)->not->toBeNull();
            
            // Delete draft
            $this->action->deleteDraft($this->conversation, $this->user->id);
            
            // Verify draft is deleted
            $result = $this->action->getDraft($this->conversation, $this->user->id);
            expect($result)->toBeNull();
        });

        test('handles deleting non-existent draft gracefully', function () {
            // Should not throw exception
            $this->action->deleteDraft($this->conversation, $this->user->id);
            
            // Verify no draft exists
            $result = $this->action->getDraft($this->conversation, $this->user->id);
            expect($result)->toBeNull();
        });
    });

    describe('user isolation', function () {
        test('drafts are user-specific', function () {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();
            
            $message1 = 'User 1 message';
            $message2 = 'User 2 message';
            
            // Save drafts for different users
            $this->action->saveDraft($this->conversation, $user1->id, $message1);
            $this->action->saveDraft($this->conversation, $user2->id, $message2);
            
            // Verify each user sees only their own draft
            $draft1 = $this->action->getDraft($this->conversation, $user1->id);
            $draft2 = $this->action->getDraft($this->conversation, $user2->id);
            
            expect($draft1['message'])->toBe($message1)
                ->and($draft1['user_id'])->toBe($user1->id)
                ->and($draft2['message'])->toBe($message2)
                ->and($draft2['user_id'])->toBe($user2->id);
        });

        test('deleting one user draft does not affect another', function () {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();
            
            $message1 = 'User 1 message';
            $message2 = 'User 2 message';
            
            // Save drafts for different users
            $this->action->saveDraft($this->conversation, $user1->id, $message1);
            $this->action->saveDraft($this->conversation, $user2->id, $message2);
            
            // Delete user 1's draft
            $this->action->deleteDraft($this->conversation, $user1->id);
            
            // Verify user 1's draft is deleted but user 2's remains
            $draft1 = $this->action->getDraft($this->conversation, $user1->id);
            $draft2 = $this->action->getDraft($this->conversation, $user2->id);
            
            expect($draft1)->toBeNull()
                ->and($draft2)->not->toBeNull()
                ->and($draft2['message'])->toBe($message2);
        });
    });
});