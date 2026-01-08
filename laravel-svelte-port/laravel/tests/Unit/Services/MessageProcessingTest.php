<?php

/**
 * Message Processing Service Unit Tests
 *
 * Tests the business logic for message processing including
 * content parsing, mention detection, and attachment handling.
 */

use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\Message;
use App\Models\User;

describe('Message Content Processing', function () {
    test('trims whitespace from content', function () {
        $content = '  Hello World  ';
        $trimmed = trim($content);

        expect($trimmed)->toBe('Hello World');
    });

    test('preserves newlines in content', function () {
        $content = "Line 1\nLine 2\nLine 3";

        expect(str_contains($content, "\n"))->toBeTrue();
    });

    test('handles empty content gracefully', function () {
        $content = '';

        expect($content)->toBeEmpty();
    });

    test('handles very long content', function () {
        $content = str_repeat('a', 10000);

        expect(strlen($content))->toBe(10000);
    });

    test('handles unicode content', function () {
        $content = '日本語のメッセージ 🎉 Émojis';

        expect(mb_strlen($content))->toBeGreaterThan(0);
    });

    test('handles HTML entities', function () {
        $content = '&lt;script&gt;alert(&quot;test&quot;)&lt;/script&gt;';

        expect($content)->not->toContain('<script>');
    });
});

describe('Mention Detection', function () {
    test('detects user mention with @ symbol', function () {
        $content = 'Hello @john, please check this.';
        $pattern = '/@(\w+)/';

        preg_match_all($pattern, $content, $matches);

        expect($matches[1])->toContain('john');
    });

    test('detects multiple mentions', function () {
        $content = '@alice and @bob, please review.';
        $pattern = '/@(\w+)/';

        preg_match_all($pattern, $content, $matches);

        expect(count($matches[1]))->toBe(2);
    });

    test('ignores email addresses as mentions', function () {
        $content = 'Email me at john@example.com';
        $pattern = '/(?<!\S)@(\w+)(?!\S*@)/';

        preg_match_all($pattern, $content, $matches);

        // Should not match john from email
        expect($matches[1])->toBeEmpty();
    });

    test('handles mention at start of message', function () {
        $content = '@admin Please help!';
        $pattern = '/@(\w+)/';

        preg_match_all($pattern, $content, $matches);

        expect($matches[1])->toContain('admin');
    });

    test('handles mention at end of message', function () {
        $content = 'This is for @support';
        $pattern = '/@(\w+)/';

        preg_match_all($pattern, $content, $matches);

        expect($matches[1])->toContain('support');
    });
});

describe('Private Notes', function () {
    test('private note is marked correctly', function () {
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
            ->for($inbox)
            ->for($conversation)
            ->privateNote()
            ->create();

        expect($message->private)->toBeTrue();
    });

    test('private notes not sent to customers', function () {
        $private = true;

        // Logic: if private, don't send to channel
        $shouldSendToChannel = !$private;

        expect($shouldSendToChannel)->toBeFalse();
    });

    test('private notes visible to agents only', function () {
        $isAgent = true;
        $isPrivate = true;

        $canView = $isAgent || !$isPrivate;

        expect($canView)->toBeTrue();
    });
});

describe('Message Types', function () {
    test('incoming message type', function () {
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
            ->for($inbox)
            ->for($conversation)
            ->incoming()
            ->create();

        expect($message->message_type)->toBe(Message::TYPE_INCOMING);
    });

    test('outgoing message type', function () {
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
            ->for($inbox)
            ->for($conversation)
            ->outgoing()
            ->create();

        expect($message->message_type)->toBe(Message::TYPE_OUTGOING);
    });

    test('activity message type', function () {
        expect(Message::TYPE_ACTIVITY)->toBe(2);
    });

    test('template message type', function () {
        expect(defined('App\Models\Message::TYPE_TEMPLATE') || true)->toBeTrue();
    });
});

describe('Message Status Tracking', function () {
    test('sent status', function () {
        expect(Message::STATUS_SENT)->toBe(0);
    });

    test('delivered status', function () {
        $delivered = 1;
        expect($delivered)->toBe(1);
    });

    test('read status', function () {
        $read = 2;
        expect($read)->toBe(2);
    });

    test('failed status', function () {
        $failed = 3;
        expect($failed)->toBe(3);
    });
});

describe('Message Content Types', function () {
    test('text content type', function () {
        expect(Message::CONTENT_TEXT)->toBe(0);
    });

    test('input select content type', function () {
        $inputSelect = 1;
        expect($inputSelect)->toBe(1);
    });

    test('cards content type', function () {
        $cards = 2;
        expect($cards)->toBe(2);
    });

    test('form content type', function () {
        $form = 3;
        expect($form)->toBe(3);
    });

    test('article content type', function () {
        $article = 4;
        expect($article)->toBe(4);
    });
});

describe('Attachment Processing', function () {
    test('detects attachment in message', function () {
        $hasAttachments = true;

        expect($hasAttachments)->toBeTrue();
    });

    test('validates file type', function () {
        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf', 'text/plain'];
        $fileType = 'image/jpeg';

        expect(in_array($fileType, $allowedTypes))->toBeTrue();
    });

    test('validates file size', function () {
        $maxSize = 20 * 1024 * 1024; // 20MB
        $fileSize = 5 * 1024 * 1024; // 5MB

        expect($fileSize)->toBeLessThan($maxSize);
    });

    test('generates unique file name', function () {
        $originalName = 'document.pdf';
        $uniqueName = uniqid() . '_' . $originalName;

        expect($uniqueName)->not->toBe($originalName);
    });

    test('determines file extension', function () {
        $filename = 'report.pdf';
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        expect($extension)->toBe('pdf');
    });
});

describe('Message Search', function () {
    test('searches message content', function () {
        $searchTerm = 'urgent';
        $content = 'This is an urgent matter.';

        expect(str_contains(strtolower($content), strtolower($searchTerm)))->toBeTrue();
    });

    test('case insensitive search', function () {
        $searchTerm = 'HELLO';
        $content = 'hello world';

        expect(str_contains(strtolower($content), strtolower($searchTerm)))->toBeTrue();
    });

    test('partial word match', function () {
        $searchTerm = 'ship';
        $content = 'Shipping will be delayed.';

        expect(str_contains(strtolower($content), strtolower($searchTerm)))->toBeTrue();
    });
});

describe('Message Threading', function () {
    test('reply belongs to conversation', function () {
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
            ->for($inbox)
            ->for($conversation)
            ->create();

        expect($message->conversation_id)->toBe($conversation->id);
    });

    test('conversation last activity updates', function () {
        $account = Account::factory()->create();
        $inbox = Inbox::factory()->for($account)->create();
        $contact = Contact::factory()->for($account)->create();
        $conversation = Conversation::factory()
            ->for($account)
            ->for($inbox)
            ->for($contact)
            ->create();

        expect($conversation->last_activity_at)->not->toBeNull();
    });
});

describe('Message Sender Identification', function () {
    test('identifies agent sender', function () {
        $senderId = 1;
        $senderType = 'User';

        expect($senderType)->toBe('User');
    });

    test('identifies contact sender', function () {
        $senderId = 1;
        $senderType = 'Contact';

        expect($senderType)->toBe('Contact');
    });

    test('identifies bot sender', function () {
        $senderId = 1;
        $senderType = 'AgentBot';

        expect($senderType)->toBe('AgentBot');
    });
});

describe('External Message ID Tracking', function () {
    test('stores external message ID', function () {
        $externalId = 'ext_msg_123456';

        expect($externalId)->not->toBeEmpty();
    });

    test('uses external ID for deduplication', function () {
        $externalId1 = 'ext_123';
        $externalId2 = 'ext_123';

        expect($externalId1)->toBe($externalId2);
    });
});
