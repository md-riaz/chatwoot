<?php

namespace App\Http\Controllers\Api\V1\Widget;

use App\Actions\Conversation\UpdateConversationAction;
use App\Events\Contact\ContactCreated;
use App\Events\Conversation\ConversationCreated;
use App\Events\Message\MessageCreated;
use App\Models\Contact;
use App\Models\ContactInbox;
use App\Models\Conversation;
use App\Models\Inbox;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConversationsController extends BaseController
{
    /**
     * Get conversations for the contact.
     * GET /api/v1/widget/conversations
     */
    public function index(Request $request): JsonResponse
    {
        $contactInbox = $this->resolveContactInbox($request);

        if (!$contactInbox) {
            return response()->json(['error' => 'Unauthorized'], 401);
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
                    'messages' => $conversation->messages,
                    'created_at' => $conversation->created_at,
                    'last_activity_at' => $conversation->last_activity_at,
                ];
            }),
        ]);
    }

    /**
     * Create a new conversation.
     * POST /api/v1/widget/conversations
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'website_token' => 'required|string',
            'contact' => 'nullable|array',
            'contact.name' => 'nullable|string|max:255',
            'contact.email' => 'nullable|email',
            'contact.phone_number' => 'nullable|string|max:50',
            'contact.identifier' => 'nullable|string',
            'contact.custom_attributes' => 'nullable|array',
            'message' => 'nullable|array',
            'message.content' => 'nullable|string',
            'custom_attributes' => 'nullable|array',
        ]);

        // Find the inbox by website token
        $inbox = Inbox::where('channel_type', 'Channel::WebWidget')
            ->whereHas('channel', function ($query) use ($validated) {
                $query->where('website_token', $validated['website_token']);
            })
            ->first();

        if (!$inbox) {
            return response()->json(['error' => 'Invalid website token'], 404);
        }

        // Get or create contact
        $contactInbox = $this->resolveContactInbox($request);
        
        if (!$contactInbox) {
            // Create a new contact
            $contact = Contact::create([
                'account_id' => $inbox->account_id,
                'name' => $validated['contact']['name'] ?? null,
                'email' => $validated['contact']['email'] ?? null,
                'phone_number' => $validated['contact']['phone_number'] ?? null,
                'identifier' => $validated['contact']['identifier'] ?? null,
                'custom_attributes' => $validated['contact']['custom_attributes'] ?? [],
            ]);

            event(new ContactCreated($contact));

            // Create contact inbox
            $contactInbox = ContactInbox::create([
                'contact_id' => $contact->id,
                'inbox_id' => $inbox->id,
                'source_id' => Str::uuid()->toString(),
            ]);
        }

        // Create the conversation
        $conversation = Conversation::create([
            'account_id' => $inbox->account_id,
            'inbox_id' => $inbox->id,
            'contact_id' => $contactInbox->contact_id,
            'contact_inbox_id' => $contactInbox->id,
            'status' => Conversation::STATUS_OPEN,
            'uuid' => Str::uuid()->toString(),
            'custom_attributes' => $validated['custom_attributes'] ?? [],
            'last_activity_at' => now(),
        ]);

        event(new ConversationCreated($conversation));

        // Create initial message if provided
        if (!empty($validated['message']['content'])) {
            $message = $conversation->messages()->create([
                'account_id' => $inbox->account_id,
                'inbox_id' => $inbox->id,
                'content' => $validated['message']['content'],
                'message_type' => Message::TYPE_INCOMING,
                'sender_type' => Contact::class,
                'sender_id' => $contactInbox->contact_id,
            ]);

            event(new MessageCreated($message));
        }

        return response()->json([
            'id' => $conversation->id,
            'uuid' => $conversation->uuid,
            'status' => $conversation->status,
            'contact' => [
                'id' => $contactInbox->contact_id,
                'pubsub_token' => $contactInbox->source_id,
            ],
            'messages' => $conversation->messages,
            'created_at' => $conversation->created_at,
        ], 201);
    }

    /**
     * Toggle conversation status.
     * GET /api/v1/widget/conversations/toggle_status
     */
    public function toggleStatus(Request $request): JsonResponse
    {
        $contactInbox = $this->resolveContactInbox($request);

        if (!$contactInbox) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get the most recent open conversation
        $conversation = Conversation::where('contact_inbox_id', $contactInbox->id)
            ->where('status', Conversation::STATUS_OPEN)
            ->latest()
            ->first();

        if (!$conversation) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        $conversation = UpdateConversationAction::run($conversation, [
            'status' => Conversation::STATUS_RESOLVED,
        ]);

        return response()->json([
            'id' => $conversation->id,
            'status' => $conversation->status,
        ]);
    }

    /**
     * Toggle typing status.
     * POST /api/v1/widget/conversations/toggle_typing
     */
    public function toggleTyping(Request $request): JsonResponse
    {
        $contactInbox = $this->resolveContactInbox($request);

        if (!$contactInbox) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'typing_status' => 'required|in:on,off',
        ]);

        // Broadcast typing status event
        // event(new TypingStatusChanged($contactInbox, $validated['typing_status']));

        return response()->json(null, 200);
    }

    /**
     * Update last seen timestamp.
     * POST /api/v1/widget/conversations/update_last_seen
     */
    public function updateLastSeen(Request $request): JsonResponse
    {
        $contactInbox = $this->resolveContactInbox($request);

        if (!$contactInbox) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $conversation = Conversation::where('contact_inbox_id', $contactInbox->id)
            ->latest()
            ->first();

        if ($conversation) {
            $conversation->update(['contact_last_seen_at' => now()]);
        }

        return response()->json(null, 200);
    }

    /**
     * Set custom attributes on conversation.
     * POST /api/v1/widget/conversations/set_custom_attributes
     */
    public function setCustomAttributes(Request $request): JsonResponse
    {
        $contactInbox = $this->resolveContactInbox($request);

        if (!$contactInbox) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'custom_attributes' => 'required|array',
        ]);

        $conversation = Conversation::where('contact_inbox_id', $contactInbox->id)
            ->latest()
            ->first();

        if (!$conversation) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        UpdateConversationAction::run($conversation, [
            'custom_attributes' => array_merge(
                $conversation->custom_attributes ?? [],
                $validated['custom_attributes']
            ),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Destroy custom attributes from conversation.
     * POST /api/v1/widget/conversations/destroy_custom_attributes
     */
    public function destroyCustomAttributes(Request $request): JsonResponse
    {
        $contactInbox = $this->resolveContactInbox($request);

        if (!$contactInbox) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'custom_attributes' => 'required|array',
        ]);

        $conversation = Conversation::where('contact_inbox_id', $contactInbox->id)
            ->latest()
            ->first();

        if (!$conversation) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        $customAttributes = $conversation->custom_attributes ?? [];

        foreach ($validated['custom_attributes'] as $key) {
            unset($customAttributes[$key]);
        }

        UpdateConversationAction::run($conversation, ['custom_attributes' => $customAttributes]);

        return response()->json(['success' => true]);
    }

    /**
     * Request transcript.
     * POST /api/v1/widget/conversations/transcript
     */
    public function transcript(Request $request): JsonResponse
    {
        $contactInbox = $this->resolveContactInbox($request);

        if (!$contactInbox) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $conversation = Conversation::where('contact_inbox_id', $contactInbox->id)
            ->latest()
            ->first();

        if (!$conversation) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        // Queue transcript email job
        // TranscriptEmailJob::dispatch($conversation, $validated['email']);

        return response()->json(['message' => 'Transcript will be sent to your email']);
    }
}
