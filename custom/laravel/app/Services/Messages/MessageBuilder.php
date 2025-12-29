<?php

namespace App\Services\Messages;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\AgentBot;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class MessageBuilder
{
    protected Conversation $conversation;
    protected $user; // User|null
    protected array $params;

    public function __construct($user, Conversation $conversation, array $params)
    {
        $this->user = $user;
        $this->conversation = $conversation;
        $this->params = $params;
    }

    /**
     * Create and return a Message built from params.
     */
    public function perform(): Message
    {
        $msg = new Message();
        $msg->account_id = $this->conversation->account_id;
        $msg->inbox_id = $this->conversation->inbox_id;
        $msg->conversation_id = $this->conversation->id;

        $messageType = $this->params['message_type'] ?? 'outgoing';
        $msg->message_type = $messageType === 'incoming' ? Message::TYPE_INCOMING : Message::TYPE_OUTGOING;

        $msg->content = $this->params['content'] ?? null;
        $msg->private = $this->params['private'] ?? false;

        if (isset($this->params['content_type'])) {
            $msg->content_type = $this->params['content_type'];
        }

        // content_attributes may be an array or JSON string
        $contentAttrs = $this->params['content_attributes'] ?? [];
        if (is_string($contentAttrs)) {
            $decoded = json_decode($contentAttrs, true);
            $contentAttrs = $decoded === null ? [] : $decoded;
        }
        $msg->content_attributes = $contentAttrs ?: null;

        // sender resolution
        if (($messageType === 'outgoing') && ($this->params['sender_type'] ?? null) === 'AgentBot') {
            $agent = AgentBot::where('id', $this->params['sender_id'] ?? null)->first();
            if ($agent) {
                $msg->sender_id = $agent->id;
                $msg->sender_type = get_class($agent);
            }
        } elseif ($messageType === 'incoming') {
            // incoming message sender is the conversation contact
            $msg->sender_id = $this->conversation->contact_id;
            $msg->sender_type = \App\Models\Contact::class;
        } elseif ($this->user instanceof User) {
            $msg->sender_id = $this->user->id;
            $msg->sender_type = get_class($this->user);
        }

        if (!empty($this->params['external_created_at'])) {
            $msg->content_attributes = array_merge($msg->content_attributes ?? [], ['external_created_at' => $this->params['external_created_at']]);
        }

        // save and dispatch
        try {
            $msg->save();
            event(new \App\Events\Message\MessageCreated($msg));
        } catch (\Exception $e) {
            Log::error('MessageBuilder failed to save message', ['error' => $e->getMessage(), 'params' => $this->params]);
            throw $e;
        }

        return $msg;
    }
}
