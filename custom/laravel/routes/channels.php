<?php

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
    $conversation = \App\Models\Conversation::find($conversationId);
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
