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
        // Ensure user has access to account
        abort_unless(request()->user()->accounts()->where('account_id', $account->id)->exists(), 404);

        // Ensure conversation belongs to account
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

        $assignee = $request->has('assignee_id')
            ? User::findOrFail($request->assignee_id)
            : null;

        $updatedConversation = AssignConversationAction::run($conversation, $assignee);

        return new ConversationResource($updatedConversation->load('contact', 'inbox', 'assignee'));
    }

    /**
     * Resolve/close the conversation.
     */
    public function resolve(Account $account, Conversation $conversation): ConversationResource
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $updatedConversation = CloseConversationAction::run($conversation);

        return new ConversationResource($updatedConversation->load('contact', 'inbox', 'assignee'));
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
