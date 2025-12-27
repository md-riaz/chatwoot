<?php

namespace App\Http\Controllers\Api\V1\Public\Inboxes;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactInbox;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    /**
     * Get messages for a conversation.
     * GET /api/v1/public/inboxes/{inbox}/contacts/{contact}/conversations/{conversation}/messages
     */
    public function index(Request $request, Inbox $inbox, Contact $contact, Conversation $conversation): JsonResponse
    {
        $contactInbox = ContactInbox::where('contact_id', $contact->id)
            ->where('inbox_id', $inbox->id)
            ->first();

        if (!$contactInbox || $conversation->contact_inbox_id !== $contactInbox->id) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        $validated = $request->validate([
            'before' => 'nullable|integer',
            'after' => 'nullable|integer',
        ]);

        $query = $conversation->messages()
            ->with(['sender', 'attachments'])
            ->orderBy('id', 'desc')
            ->limit(20);

        if (isset($validated['before'])) {
            $query->where('id', '<', $validated['before']);
        }

        if (isset($validated['after'])) {
            $query->where('id', '>', $validated['after']);
        }

        $messages = $query->get()->reverse()->values();

        return response()->json([
            'data' => $messages->map(function ($message) {
                return $this->formatMessage($message);
            }),
        ]);
    }

    /**
     * Create a new message.
     * POST /api/v1/public/inboxes/{inbox}/contacts/{contact}/conversations/{conversation}/messages
     */
    public function store(Request $request, Inbox $inbox, Contact $contact, Conversation $conversation): JsonResponse
    {
        $contactInbox = ContactInbox::where('contact_id', $contact->id)
            ->where('inbox_id', $inbox->id)
            ->first();

        if (!$contactInbox || $conversation->contact_inbox_id !== $contactInbox->id) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        $validated = $request->validate([
            'content' => 'required_without:attachments|string',
            'echo_id' => 'nullable|string',
        ]);

        // Create the message
        $message = Message::create([
            'account_id' => $inbox->account_id,
            'inbox_id' => $inbox->id,
            'conversation_id' => $conversation->id,
            'content' => $validated['content'] ?? '',
            'message_type' => 0, // incoming
            'sender_type' => Contact::class,
            'sender_id' => $contact->id,
            'external_source_id_echo' => $validated['echo_id'] ?? null,
        ]);

        // Update conversation last activity
        $conversation->update(['last_activity_at' => now()]);

        return response()->json($this->formatMessage($message), 201);
    }

    /**
     * Update a message.
     * PATCH /api/v1/public/inboxes/{inbox}/contacts/{contact}/conversations/{conversation}/messages/{message}
     */
    public function update(Request $request, Inbox $inbox, Contact $contact, Conversation $conversation, Message $message): JsonResponse
    {
        $contactInbox = ContactInbox::where('contact_id', $contact->id)
            ->where('inbox_id', $inbox->id)
            ->first();

        if (!$contactInbox || $conversation->contact_inbox_id !== $contactInbox->id) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        if ($message->conversation_id !== $conversation->id) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        $validated = $request->validate([
            'submitted_email' => 'nullable|email',
            'submitted_values' => 'nullable|array',
        ]);

        $message->update([
            'content_attributes' => array_merge(
                $message->content_attributes ?? [],
                $validated
            ),
        ]);

        return response()->json($this->formatMessage($message));
    }

    /**
     * Format a message for the response.
     */
    private function formatMessage(Message $message): array
    {
        return [
            'id' => $message->id,
            'content' => $message->content,
            'message_type' => $message->message_type,
            'content_type' => $message->content_type,
            'content_attributes' => $message->content_attributes,
            'created_at' => $message->created_at?->timestamp,
            'conversation_id' => $message->conversation_id,
            'attachments' => $message->attachments?->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'file_type' => $attachment->file_type,
                    'data_url' => $attachment->data_url ?? $attachment->file_path,
                ];
            }) ?? [],
            'sender' => $message->sender ? [
                'id' => $message->sender->id,
                'name' => $message->sender->name ?? 'Agent',
                'avatar_url' => $message->sender->avatar_url ?? null,
            ] : null,
        ];
    }
}
