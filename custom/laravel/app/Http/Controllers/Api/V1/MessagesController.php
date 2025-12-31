<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Message\CreateMessageAction;
use App\Actions\Message\DeleteMessageAction;
use App\Actions\Message\UpdateMessageAction;
use App\Data\Message\MessageData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Message\StoreMessageRequest;
use App\Http\Resources\Message\MessageResource;
use App\Models\Account;
use App\Models\Conversation;
use App\Models\Message;
use App\Repositories\Message\MessageRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\TranslationService;
use App\Jobs\SendReplyJob;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MessagesController extends Controller
{
    public function __construct(
        private MessageRepository $messageRepository
    ) {}

    /**
     * Display a listing of messages for a conversation.
     */
    public function index(Account $account, Conversation $conversation, Request $request): AnonymousResourceCollection
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $messages = $this->messageRepository->getForConversation(
            $conversation->id,
            $request->get('per_page', 50)
        );

        return MessageResource::collection($messages);
    }

    /**
     * Store a newly created message.
     */
    public function store(StoreMessageRequest $request, Account $account, Conversation $conversation): MessageResource
    {
        abort_unless($conversation->account_id === $account->id, 404);

        $data = array_merge($request->validated(), [
            'account_id' => $account->id,
            'conversation_id' => $conversation->id,
            'inbox_id' => $conversation->inbox_id,
            'sender_id' => $request->user()?->id,
            'sender_type' => $request->user() ? 'App\Models\User' : null,
            'message_type' => $request->get('message_type', Message::TYPE_OUTGOING),
        ]);

        $message = CreateMessageAction::run(MessageData::from($data));

        return new MessageResource($message->load('sender', 'attachments'));
    }

    /**
     * Display the specified message.
     */
    public function show(Account $account, Conversation $conversation, Message $message): MessageResource
    {
        abort_unless($conversation->account_id === $account->id, 404);
        abort_unless($message->conversation_id === $conversation->id, 404);

        return new MessageResource($message->load('sender', 'attachments'));
    }

    /**
     * Update the specified message.
     */
    public function update(Request $request, Account $account, Conversation $conversation, Message $message): MessageResource
    {
        abort_unless($conversation->account_id === $account->id, 404);
        abort_unless($message->conversation_id === $conversation->id, 404);

        $updatedMessage = UpdateMessageAction::run(
            $message,
            $request->only(['content', 'content_attributes'])
        );

        return new MessageResource($updatedMessage);
    }

    /**
     * Remove the specified message.
     */
    public function destroy(Account $account, Conversation $conversation, Message $message): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);
        abort_unless($message->conversation_id === $conversation->id, 404);

        DeleteMessageAction::run($message);

        return response()->json(null, 204);
    }

    /**
     * Translate a message to the target language.
     */
    public function translate(Request $request, Account $account, Conversation $conversation, Message $message): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);
        abort_unless($message->conversation_id === $conversation->id, 404);

        $validated = $request->validate([
            'target_language' => 'required|string|size:2',
        ]);

        $targetLanguage = $validated['target_language'];

        // Check if translation already exists
        $translations = $message->translations ?? [];
        if (isset($translations[$targetLanguage])) {
            return response()->json(['content' => $translations[$targetLanguage]]);
        }

        // Use the TranslationService (pluggable) to translate message content.
        $translatedContent = app(TranslationService::class)->translate($message->content, $targetLanguage);

        // Save translation
        $translations[$targetLanguage] = $translatedContent;
        $message->update(['translations' => $translations]);

        return response()->json(['content' => $translatedContent]);
    }

    /**
     * Retry sending a failed message.
     */
    public function retry(Account $account, Conversation $conversation, Message $message): JsonResponse
    {
        abort_unless($conversation->account_id === $account->id, 404);
        abort_unless($message->conversation_id === $conversation->id, 404);

        // Reset message status and dispatch a job to resend the message
        $message->update([
            'status' => Message::STATUS_SENT,
            'content_attributes' => [],
        ]);

        SendReplyJob::dispatch($message->id);

        return response()->json(['data' => new MessageResource($message)]);
    }
}
