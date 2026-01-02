<?php

use App\Actions\Assignment\AutoAssignConversationAction;
use App\Events\Conversation\ConversationAssigned;
use App\Models\Account;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\User;
use Illuminate\Support\Facades\Event;

test('auto assign dispatches ConversationAssigned', function () {
    Event::fake();

    $account = Account::factory()->create();
    $inbox = Inbox::factory()->for($account)->create(['enable_auto_assignment' => true]);

    $agent = User::factory()->create(['availability' => 1]);
    $account->users()->attach($agent->id, ['role' =>  0]);
    $inbox->users()->attach($agent->id);

    $contact = \App\Models\Contact::factory()->for($account)->create();

    $conversation = Conversation::factory()
        ->for($account)
        ->for($inbox)
        ->for($contact)
        ->unassigned()
        ->create();

    $action = app(AutoAssignConversationAction::class);
    $assigned = $action->handle($conversation->id);

    expect($assigned)->not->toBeNull();

    Event::assertDispatched(ConversationAssigned::class, function ($e) use ($conversation, $agent) {
        return $e->conversation->id === $conversation->fresh()->id
            && $e->assignee->id === $agent->id;
    });
});
