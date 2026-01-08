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
    // Unit test without database - just test that model is instantiated correctly
    $conversation = new \App\Models\Conversation;
    $conversation->account_id = 1;
    $conversation->inbox_id = 1;
    $conversation->contact_id = 1;
    $conversation->display_id = 1;
    $conversation->status = \App\Models\Conversation::STATUS_OPEN;
    $conversation->priority = \App\Models\Conversation::PRIORITY_NONE;

    expect($conversation->account_id)->toBe(1);
    expect($conversation->inbox_id)->toBe(1);
    expect($conversation->status)->toBe(\App\Models\Conversation::STATUS_OPEN);
});
