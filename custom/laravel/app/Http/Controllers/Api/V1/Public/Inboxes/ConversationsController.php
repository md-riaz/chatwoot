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
use Illuminate\Support\Str;

class ConversationsController extends Controller
{
    /**
     * Get conversations for a contact.
     * GET /api/v1/public/inboxes/{inbox}/contacts/{contact}/conversations
     */
    public function index(Inbox $inbox, Contact $contact): JsonResponse
    {
        $contactInbox = ContactInbox::where('contact_id', $contact->id)
            ->where('inbox_id', $inbox->id)
            ->first();

        if (!$contactInbox) {
            return response()->json(['error' => 'Contact not found'], 404);
        }

        $conversations = Conversation::where('contact_inbox_id', $contactInbox->id)
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->orderByDesc('last_activity_at')
            ->get();

        return response()->json([
            'data' => $conversations->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'uuid' => $conversation->uuid,
                    'status' => $conversation->status,
                    'created_at' => $conversation->created_at,
                    'last_activity_at' => $conversation->last_activity_at,
                    'messages' => $conversation->messages,
                ];
            }),
        ]);
    }

    /**
     * Create a new conversation.
     * POST /api/v1/public/inboxes/{inbox}/contacts/{contact}/conversations
     */
    public function store(Request $request, Inbox $inbox, Contact $contact): JsonResponse
    {
        $contactInbox = ContactInbox::where('contact_id', $contact->id)
            ->where('inbox_id', $inbox->id)
            ->first();

        if (!$contactInbox) {
            // Create contact inbox if it doesn't exist
            $contactInbox = ContactInbox::create([
                'contact_id' => $contact->id,
                'inbox_id' => $inbox->id,
                'source_id' => Str::uuid()->toString(),
            ]);
        }

        $validated = $request->validate([
            'message' => 'nullable|array',
            'message.content' => 'nullable|string',
            'custom_attributes' => 'nullable|array',
        ]);

        // Create the conversation
        $conversation = Conversation::create([
            'account_id' => $inbox->account_id,
            'inbox_id' => $inbox->id,
            'contact_id' => $contact->id,
            'contact_inbox_id' => $contactInbox->id,
            'status' => 'open',
            'uuid' => Str::uuid()->toString(),
            'custom_attributes' => $validated['custom_attributes'] ?? [],
            'last_activity_at' => now(),
        ]);

        // Create initial message if provided
        if (!empty($validated['message']['content'])) {
            Message::create([
                'account_id' => $inbox->account_id,
                'inbox_id' => $inbox->id,
                'conversation_id' => $conversation->id,
                'content' => $validated['message']['content'],
                'message_type' => 0, // incoming
                'sender_type' => Contact::class,
                'sender_id' => $contact->id,
            ]);
        }

        return response()->json([
            'id' => $conversation->id,
            'uuid' => $conversation->uuid,
            'status' => $conversation->status,
            'created_at' => $conversation->created_at,
        ], 201);
    }

    /**
     * Display a conversation.
     * GET /api/v1/public/inboxes/{inbox}/contacts/{contact}/conversations/{conversation}
     */
    public function show(Inbox $inbox, Contact $contact, Conversation $conversation): JsonResponse
    {
        $contactInbox = ContactInbox::where('contact_id', $contact->id)
            ->where('inbox_id', $inbox->id)
            ->first();

        if (!$contactInbox || $conversation->contact_inbox_id !== $contactInbox->id) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        return response()->json([
            'id' => $conversation->id,
            'uuid' => $conversation->uuid,
            'status' => $conversation->status,
            'custom_attributes' => $conversation->custom_attributes,
            'created_at' => $conversation->created_at,
            'last_activity_at' => $conversation->last_activity_at,
        ]);
    }

    /**
     * Toggle conversation status.
     * POST /api/v1/public/inboxes/{inbox}/contacts/{contact}/conversations/{conversation}/toggle_status
     */
    public function toggleStatus(Inbox $inbox, Contact $contact, Conversation $conversation): JsonResponse
    {
        $contactInbox = ContactInbox::where('contact_id', $contact->id)
            ->where('inbox_id', $inbox->id)
            ->first();

        if (!$contactInbox || $conversation->contact_inbox_id !== $contactInbox->id) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        $conversation->update([
            'status' => $conversation->status === 'open' ? 'resolved' : 'open',
        ]);

        return response()->json([
            'id' => $conversation->id,
            'status' => $conversation->status,
        ]);
    }

    /**
     * Toggle typing status.
     * POST /api/v1/public/inboxes/{inbox}/contacts/{contact}/conversations/{conversation}/toggle_typing
     */
    public function toggleTyping(Request $request, Inbox $inbox, Contact $contact, Conversation $conversation): JsonResponse
    {
        $contactInbox = ContactInbox::where('contact_id', $contact->id)
            ->where('inbox_id', $inbox->id)
            ->first();

        if (!$contactInbox || $conversation->contact_inbox_id !== $contactInbox->id) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        $validated = $request->validate([
            'typing_status' => 'required|in:on,off',
        ]);

        // Broadcast typing status
        // event(new TypingStatusChanged($conversation, $contact, $validated['typing_status']));

        return response()->json(null, 200);
    }

    /**
     * Update last seen.
     * POST /api/v1/public/inboxes/{inbox}/contacts/{contact}/conversations/{conversation}/update_last_seen
     */
    public function updateLastSeen(Inbox $inbox, Contact $contact, Conversation $conversation): JsonResponse
    {
        $contactInbox = ContactInbox::where('contact_id', $contact->id)
            ->where('inbox_id', $inbox->id)
            ->first();

        if (!$contactInbox || $conversation->contact_inbox_id !== $contactInbox->id) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        $conversation->update(['contact_last_seen_at' => now()]);

        return response()->json(null, 200);
    }
}
