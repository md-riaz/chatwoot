<?php

use App\Models\Conversation;

test('conversation has correct status constants', function () {
    expect(Conversation::STATUS_OPEN)->toBe(0);
    expect(Conversation::STATUS_RESOLVED)->toBe(1);
    expect(Conversation::STATUS_PENDING)->toBe(2);
    expect(Conversation::STATUS_SNOOZED)->toBe(3);
});

test('conversation has correct priority constants', function () {
    expect(Conversation::PRIORITY_NONE)->toBe(0);
    expect(Conversation::PRIORITY_LOW)->toBe(1);
    expect(Conversation::PRIORITY_MEDIUM)->toBe(2);
    expect(Conversation::PRIORITY_HIGH)->toBe(3);
    expect(Conversation::PRIORITY_URGENT)->toBe(4);
});

test('conversation generates uuid on creation', function () {
    $conversation = Conversation::factory()->make();
    $conversation->save();

    expect($conversation->uuid)->not->toBeNull();
    expect($conversation->uuid)->toBeString();
});
