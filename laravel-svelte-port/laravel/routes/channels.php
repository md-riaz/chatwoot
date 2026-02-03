<?php

use App\Models\Article;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\Portal;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Account channel - for all users in an account
Broadcast::channel('account.{accountId}', function ($user, $accountId) {
    return $user->accounts()->where('account_id', $accountId)->exists();
});

// Conversation channel - for specific conversation updates
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);
    if (! $conversation) {
        return false;
    }

    return $user->accounts()->where('account_id', $conversation->account_id)->exists();
});

// Presence channel for online agents
Broadcast::channel('account.{accountId}.presence', function ($user, $accountId) {
    if ($user->accounts()->where('account_id', $accountId)->exists()) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar_url,
        ];
    }

    return false;
});

// User-specific private channel for direct assignment/notifications
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Portal channel - scoped to account membership
Broadcast::channel('portal.{portalId}', function ($user, $portalId) {
    $portal = Portal::find($portalId);

    if (! $portal) {
        return false;
    }

    return $user->accounts()->where('account_id', $portal->account_id)->exists();
});

// Article channel - scoped to account membership via portal/account
Broadcast::channel('article.{articleId}', function ($user, $articleId) {
    $article = Article::find($articleId);

    if (! $article) {
        return false;
    }

    return $user->accounts()->where('account_id', $article->account_id)->exists();
});

// Contact-based channels for widget support
Broadcast::channel('contact.{contactId}', function ($user, $contactId) {
    // For authenticated contacts (widget users)
    if ($user instanceof Contact) {
        return (int) $user->id === (int) $contactId;
    }
    
    // For agents viewing contact conversations
    $contact = Contact::find($contactId);
    if (!$contact) {
        return false;
    }
    
    return $user->accounts()->where('account_id', $contact->account_id)->exists();
});

// Contact presence channels
Broadcast::channel('contact.{contactId}.presence', function ($user, $contactId) {
    if ($user instanceof Contact && (int) $user->id === (int) $contactId) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar_url' => $user->avatar_url ?? null,
            'type' => 'contact'
        ];
    }
    
    return false;
});

// Inbox-specific channels for widget conversations
Broadcast::channel('inbox.{inboxId}', function ($user, $inboxId) {
    $inbox = Inbox::find($inboxId);
    if (!$inbox) {
        return false;
    }
    
    // For contacts - check if they have conversations in this inbox
    if ($user instanceof Contact) {
        return $user->conversations()->where('inbox_id', $inboxId)->exists();
    }
    
    // For agents - check account membership
    return $user->accounts()->where('account_id', $inbox->account_id)->exists();
});
