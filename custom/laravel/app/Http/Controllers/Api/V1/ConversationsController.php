<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Conversation\AssignConversationAction;
use App\Actions\Conversation\CloseConversationAction;
use App\Actions\Conversation\CreateConversationAction;
use App\Actions\Conversation\UpdateConversationAction;
use App\Data\Conversation\ConversationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Conversation\StoreConversationRequest;
use App\Http\Resources\Conversation\ConversationResource;
use App\Models\Account;
use App\Models\Conversation;
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
            $request->only(['status', 'priority', 'custom_attributes', 'snoozed_until'])
        );

        return new ConversationResource($updatedConversation->load('contact', 'inbox', 'assignee'));
    }

    /**
     * Assign the conversation to an agent.
     */
    public function assign(Request $request, Account $account, Conversation $conversation): ConversationResource
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $assignee = null;
        if ($request->has('assignee_id') && ! is_null($request->assignee_id)) {
            $assignee = User::findOrFail($request->assignee_id);
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

        if ($request->has('status')) {
            $conversation->status = $request->input('status');
            if ($request->has('snoozed_until')) {
                $conversation->snoozed_until = $request->input('snoozed_until');
            }
        } else {
            $conversation->status = $conversation->status === 'open' ? 'resolved' : 'open';
        }

        $conversation->save();

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

        // Queue transcript email job
        // TranscriptEmailJob::dispatch($conversation, $request->input('email'));

        return response()->json(null, 200);
    }

    /**
     * Toggle conversation priority.
     */
    public function togglePriority(Request $request, Account $account, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $conversation->update(['priority' => $request->input('priority')]);

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

        $conversation->update([
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
}
