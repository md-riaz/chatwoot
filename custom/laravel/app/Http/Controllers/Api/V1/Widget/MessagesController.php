<?php

namespace App\Http\Controllers\Api\V1\Widget;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessagesController extends BaseController
{
    /**
     * Get messages for the current conversation.
     * GET /api/v1/widget/messages
     */
    public function index(Request $request): JsonResponse
    {
        $contactInbox = $this->resolveContactInbox($request);

        if (!$contactInbox) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'before' => 'nullable|integer',
            'after' => 'nullable|integer',
        ]);

        // Get the most recent open conversation
        $conversation = Conversation::where('contact_inbox_id', $contactInbox->id)
            ->latest()
            ->first();

        if (!$conversation) {
            return response()->json(['data' => [], 'meta' => ['has_more' => false]]);
        }

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
            'meta' => [
                'contact' => [
                    'id' => $contactInbox->contact_id,
                    'name' => $contactInbox->contact->name,
                    'email' => $contactInbox->contact->email,
                ],
            ],
        ]);
    }

    /**
     * Create a new message.
     * POST /api/v1/widget/messages
     */
    public function store(Request $request): JsonResponse
    {
        $contactInbox = $this->resolveContactInbox($request);

        if (!$contactInbox) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'content' => 'required_without:attachments|string',
            'echo_id' => 'nullable|string',
            'attachments' => 'nullable|array',
        ]);

        // Get or create conversation
        $conversation = Conversation::where('contact_inbox_id', $contactInbox->id)
            ->where('status', '!=', 'resolved')
            ->latest()
            ->first();

        if (!$conversation) {
            // Create a new conversation
            $conversation = Conversation::create([
                'account_id' => $contactInbox->inbox->account_id,
                'inbox_id' => $contactInbox->inbox_id,
                'contact_id' => $contactInbox->contact_id,
                'contact_inbox_id' => $contactInbox->id,
                'status' => 'open',
                'uuid' => \Illuminate\Support\Str::uuid()->toString(),
                'last_activity_at' => now(),
            ]);
        }

        // Create the message
        $message = Message::create([
            'account_id' => $contactInbox->inbox->account_id,
            'inbox_id' => $contactInbox->inbox_id,
            'conversation_id' => $conversation->id,
            'content' => $validated['content'] ?? '',
            'message_type' => 0, // incoming
            'sender_type' => \App\Models\Contact::class,
            'sender_id' => $contactInbox->contact_id,
            'external_source_id_echo' => $validated['echo_id'] ?? null,
        ]);

        // Update conversation last activity
        $conversation->update(['last_activity_at' => now()]);

        // Handle attachments
        if (!empty($validated['attachments'])) {
            foreach ($validated['attachments'] as $attachment) {
                $message->attachments()->create([
                    'account_id' => $contactInbox->inbox->account_id,
                    'file_type' => $attachment['file_type'] ?? 'file',
                    'file_path' => $attachment['file_path'] ?? '',
                ]);
            }
        }

        return response()->json($this->formatMessage($message->load('attachments')), 201);
    }

    /**
     * Update a message.
     * PATCH /api/v1/widget/messages/{message}
     */
    public function update(Request $request, Message $message): JsonResponse
    {
        $contactInbox = $this->resolveContactInbox($request);

        if (!$contactInbox) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Verify the message belongs to the contact's conversation
        $conversation = $message->conversation;
        if ($conversation->contact_inbox_id !== $contactInbox->id) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        // Only allow updating certain fields for submitted rating/feedback
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
                'type' => $message->sender_type === \App\Models\Contact::class ? 'contact' : 'agent',
            ] : null,
        ];
    }
}
