<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Conversation\AssignConversationAction;
use App\Actions\Conversation\CloseConversationAction;
use App\Actions\Conversation\CreateConversationAction;
use App\Actions\Conversation\UpdateConversationAction;
use App\Actions\Conversation\SendTranscriptAction;
use App\Data\Conversation\ConversationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Conversation\StoreConversationRequest;
use App\Http\Resources\Conversation\ConversationResource;
use App\Models\Account;
use App\Models\Conversation;
use App\Models\ReportingEvent;
use App\Models\User;
use App\Repositories\Conversation\ConversationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ConversationsController extends Controller
{
    private const ATTACHMENT_RESULTS_PER_PAGE = 100;

    public function __construct(
        private ConversationRepository $conversationRepository
    ) {}

    /**
     * Get inbox assistant info for a conversation (enterprise feature).
     */
    public function inboxAssistant(Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);
        
        // Check if account has enterprise features enabled
        if (!$account->feature_enabled('inbox_assistant')) {
            return response()->json(['error' => 'Inbox assistant feature not available'], 403);
        }
        
        // Get active agent bot for the inbox
        $agentBot = $conversation->inbox->agentBot;
        
        if (!$agentBot || !$agentBot->active) {
            return response()->json(['assistant' => null]);
        }
        
        // Get assistant configuration and status
        $assistantData = [
            'id' => $agentBot->id,
            'name' => $agentBot->name,
            'description' => $agentBot->description,
            'status' => $agentBot->status,
            'bot_type' => $agentBot->bot_type,
            'configuration' => $agentBot->bot_config,
            'last_activity' => $agentBot->updated_at,
        ];
        
        return response()->json(['assistant' => $assistantData]);
    }

    /**
     * Get reporting events for a conversation (enterprise feature).
     */
    public function reportingEvents(Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);
        
        // Check if account has reporting features enabled
        if (!$account->feature_enabled('advanced_reporting')) {
            return response()->json(['error' => 'Advanced reporting feature not available'], 403);
        }
        
        $events = ReportingEvent::where('conversation_id', $conversation->id)
            ->where('account_id', $account->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'value' => $event->value,
                    'value_in_business_hours' => $event->value_in_business_hours,
                    'event_start_time' => $event->event_start_time,
                    'event_end_time' => $event->event_end_time,
                    'created_at' => $event->created_at,
                    'user' => $event->user ? [
                        'id' => $event->user->id,
                        'name' => $event->user->name,
                    ] : null,
                ];
            });
        
        return response()->json(['events' => $events]);
    }

    /**
     * Display a listing of conversations for an account.
     */
    public function index(Account $account, Request $request): AnonymousResourceCollection
    {
        $conversations = $this->conversationRepository->findForAccount(
            $account->id,
            $request->only(['status', 'assignee_id', 'inbox_id', 'team_id', 'priority', 'per_page'])
        );

        return ConversationResource::collection($conversations);
    }

    /**
     * Get conversation metadata/counts.
     */
    public function meta(Account $account, Request $request): JsonResponse
    {
        $counts = $this->conversationRepository->getMetaForAccount(
            $account->id,
            $request->only(['inbox_id', 'team_id', 'assignee_id'])
        );

        return response()->json(['meta' => $counts]);
    }

    /**
     * Search conversations.
     */
    public function search(Account $account, Request $request): AnonymousResourceCollection
    {
        $conversations = $this->conversationRepository->search(
            $account->id,
            $request->input('q'),
            $request->only(['status', 'assignee_id', 'inbox_id', 'per_page'])
        );

        return ConversationResource::collection($conversations);
    }

    /**
     * Filter conversations with advanced filters.
     */
    public function filter(Request $request, Account $account): JsonResponse
    {
        $result = $this->conversationRepository->filter(
            $account->id,
            $request->input('payload', [])
        );

        return response()->json([
            'data' => ConversationResource::collection($result['conversations']),
            'meta' => ['count' => $result['count']],
        ]);
    }

    /**
     * Store a newly created conversation.
     */
    public function store(StoreConversationRequest $request, Account $account): ConversationResource
    {
        $data = array_merge($request->validated(), ['account_id' => $account->id]);

        $conversation = CreateConversationAction::run(
            ConversationData::from($data)
        );

        return new ConversationResource($conversation->load('contact', 'inbox', 'assignee'));
    }
    /**
     * Add labels to a conversation
     */
    public function addLabels(Request $request, Account $account, Conversation $conversation): ConversationResource
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $this->validate($request, ['labels' => 'required|array']);

        \App\Actions\Conversation\AddLabelsToConversationAction::run($conversation, $request->input('labels'));

        return new ConversationResource($conversation->fresh()->load('labels'));
    }

    /**
     * Remove labels from a conversation
     */
    public function removeLabels(Request $request, Account $account, Conversation $conversation): ConversationResource
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $this->validate($request, ['labels' => 'required|array']);

        \App\Actions\Conversation\RemoveLabelsFromConversationAction::run($conversation, $request->input('labels'));

        return new ConversationResource($conversation->fresh()->load('labels'));
    }
    /**
     * Display the specified conversation.
     */
    public function show(Account $account, Conversation $conversation): ConversationResource
    {
        abort_unless(request()->user()->accounts()->where('account_id', $account->id)->exists(), 404);
        abort_unless($conversation->account_id === $account->id, 404);

        return new ConversationResource(
            $conversation->load('contact', 'inbox', 'assignee', 'team')
        );
    }

    /**
     * Update the specified conversation.
     */
    public function update(Request $request, Account $account, Conversation $conversation): ConversationResource
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $updatedConversation = UpdateConversationAction::run(
            $conversation,
            $request->only(['status', 'priority', 'custom_attributes', 'snoozed_until', 'assignee_id', 'team_id', 'assigneeId', 'teamId'])
        );

        return new ConversationResource($updatedConversation->load('contact', 'inbox', 'assignee', 'team'));
    }

    /**
     * Assign the conversation to an agent.
     */
    public function assign(Request $request, Account $account, Conversation $conversation): ConversationResource
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $assigneeId = $request->input('assignee_id') ?? $request->input('assigneeId');
        $assignee = null;
        if (! is_null($assigneeId)) {
            $assignee = User::findOrFail($assigneeId);
        }

        $updatedConversation = AssignConversationAction::run($conversation, $assignee);

        return new ConversationResource($updatedConversation->load('contact', 'inbox', 'assignee'));
    }

    /**
     * Toggle conversation status (resolve/reopen).
     */
    public function toggleStatus(Request $request, Account $account, Conversation $conversation): ConversationResource
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $status = $this->normalizeStatus($request, $conversation);
        $payload = ['status' => $status];

        if ($request->has('snoozed_until')) {
            $payload['snoozed_until'] = $request->input('snoozed_until');
        }

        $conversation = UpdateConversationAction::run($conversation, $payload);

        return new ConversationResource($conversation->load('contact', 'inbox', 'assignee'));
    }

    /**
     * Resolve/close the conversation (legacy endpoint).
     */
    public function resolve(Account $account, Conversation $conversation): ConversationResource
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $updatedConversation = CloseConversationAction::run($conversation);

        return new ConversationResource($updatedConversation->load('contact', 'inbox', 'assignee'));
    }

    /**
     * Mute a conversation.
     */
    public function mute(Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $conversation->update(['muted' => true]);

        return response()->json(null, 200);
    }

    /**
     * Unmute a conversation.
     */
    public function unmute(Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $conversation->update(['muted' => false]);

        return response()->json(null, 200);
    }

    /**
     * Send conversation transcript via email.
     */
    public function transcript(Request $request, Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        if (empty($request->input('email'))) {
            return response()->json(['error' => 'email param missing'], 422);
        }

        // Split comma separated emails and queue transcript action
        $emails = array_filter(explode(',', str_replace(' ', '', $request->input('email'))));

        \App\Actions\Conversation\SendTranscriptAction::run($conversation, $emails);

        return response()->json(null, 200);
    }

    /**
     * Toggle conversation priority.
     */
    public function togglePriority(Request $request, Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $this->validate($request, ['priority' => 'nullable']);

        \App\Actions\Conversation\ChangePriorityAction::run($conversation, $request->input('priority'));

        return response()->json(null, 200);
    }

    /**
     * Toggle typing status.
     */
    public function toggleTypingStatus(Request $request, Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        // Broadcast typing status via websocket
        // event(new TypingStatusChanged($conversation, $request->user(), $request->input('typing_status')));

        return response()->json(null, 200);
    }

    /**
     * Update last seen timestamp.
     */
    public function updateLastSeen(Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $conversation->update(['agent_last_seen_at' => now()]);

        return response()->json(null, 200);
    }

    /**
     * Mark conversation as unread.
     */
    public function unread(Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $lastIncomingMessage = $conversation->messages()->where('message_type', 0)->latest()->first();
        $lastSeenAt = $lastIncomingMessage ? $lastIncomingMessage->created_at->subSecond() : null;

        $conversation->update([
            'agent_last_seen_at' => $lastSeenAt,
            'assignee_last_seen_at' => $lastSeenAt,
        ]);

        return response()->json(null, 200);
    }

    /**
     * Update custom attributes.
     */
    public function customAttributes(Request $request, Account $account, Conversation $conversation): ConversationResource
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $conversation = UpdateConversationAction::run($conversation, [
            'custom_attributes' => $request->input('custom_attributes', []),
        ]);

        return new ConversationResource($conversation->load('contact', 'inbox', 'assignee'));
    }

    /**
     * Get conversation attachments.
     */
    public function attachments(Request $request, Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $attachments = $conversation->attachments()
            ->with('message')
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', self::ATTACHMENT_RESULTS_PER_PAGE));

        return response()->json([
            'data' => $attachments->items(),
            'meta' => [
                'count' => $attachments->total(),
                'current_page' => $attachments->currentPage(),
                'per_page' => $attachments->perPage(),
            ],
        ]);
    }

    /**
     * Remove the specified conversation.
     */
    public function destroy(Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $conversation->delete();

        return response()->json(null, 204);
    }

    private function normalizeStatus(Request $request, Conversation $conversation): int
    {
        if ($request->has('status')) {
            $status = $request->input('status');
            return match ($status) {
                'open', Conversation::STATUS_OPEN => Conversation::STATUS_OPEN,
                'pending', Conversation::STATUS_PENDING => Conversation::STATUS_PENDING,
                'snoozed', Conversation::STATUS_SNOOZED => Conversation::STATUS_SNOOZED,
                default => Conversation::STATUS_RESOLVED,
            };
        }

        return $conversation->status === Conversation::STATUS_OPEN
            ? Conversation::STATUS_RESOLVED
            : Conversation::STATUS_OPEN;
    }
}
