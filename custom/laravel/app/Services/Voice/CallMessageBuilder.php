<?php

namespace App\Services\Voice;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CallMessageBuilder
{
    /**
     * Build or update a voice call message for the conversation.
     *
     * @param Conversation $conversation
     * @param string $direction
     * @param array $payload
     * @param User|null $user
     * @param array $timestamps
     * @return Message
     */
    public static function perform(Conversation $conversation, string $direction, array $payload = [], $user = null, array $timestamps = [])
    {
        try {
            $message = new Message();
            $message->account_id = $conversation->account_id;
            $message->conversation_id = $conversation->id;
            $message->inbox_id = $conversation->inbox_id;
            $message->message_type = Message::TYPE_ACTIVITY;
            $message->content_type = Message::CONTENT_ARTICLE; // use as placeholder for voice payload
            $message->content = json_encode(array_merge(['direction' => $direction], $payload));

            if ($user instanceof User) {
                $message->sender_id = $user->id;
                $message->sender_type = get_class($user);
            }

            if (!empty($timestamps['created_at'])) {
                $message->created_at = $timestamps['created_at'];
            }

            $message->save();

            // Dispatch created event for realtime clients
            try {
                event(new \App\Events\Message\MessageCreated($message));
            } catch (\Exception $e) {
                Log::warning('Failed to dispatch MessageCreated for call message', ['error' => $e->getMessage()]);
            }

            return $message;
        } catch (\Exception $e) {
            Log::error('CallMessageBuilder failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
