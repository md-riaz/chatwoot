<?php

use App\Models\Message;

test('message has correct type constants', function () {
    expect(Message::TYPE_INCOMING)->toBe(0);
    expect(Message::TYPE_OUTGOING)->toBe(1);
    expect(Message::TYPE_ACTIVITY)->toBe(2);
    expect(Message::TYPE_TEMPLATE)->toBe(3);
});

test('message has correct content type constants', function () {
    expect(Message::CONTENT_TEXT)->toBe(0);
    expect(Message::CONTENT_INPUT_TEXT)->toBe(1);
    expect(Message::CONTENT_INPUT_EMAIL)->toBe(2);
    expect(Message::CONTENT_INPUT_SELECT)->toBe(3);
    expect(Message::CONTENT_CARDS)->toBe(4);
    expect(Message::CONTENT_FORM)->toBe(5);
    expect(Message::CONTENT_ARTICLE)->toBe(6);
});

test('message has correct status constants', function () {
    expect(Message::STATUS_SENT)->toBe(0);
    expect(Message::STATUS_DELIVERED)->toBe(1);
    expect(Message::STATUS_READ)->toBe(2);
    expect(Message::STATUS_FAILED)->toBe(3);
});
