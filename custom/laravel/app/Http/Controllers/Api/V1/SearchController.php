<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\PermissionFilterService;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    public function __construct(
        private PermissionFilterService $permissionFilterService
    ) {}

    /**
     * Search across conversations, contacts, and messages.
     */
    public function index(Account $account, SearchRequest $request): JsonResponse
    {
        $user = $request->user();
        $query = $request->getQuery();
        $type = $request->getType();

        $results = [];

        if ($type === 'all' || $type === 'conversations') {
            $conversationQuery = Conversation::where('account_id', $account->id)
                ->where(function ($q) use ($query) {
                    $q->where('display_id', 'like', "%{$query}%")
                      ->orWhereHas('contact', function ($contactQuery) use ($query) {
                          $contactQuery->where('name', 'like', "%{$query}%")
                                       ->orWhere('email', 'like', "%{$query}%")
                                       ->orWhere('phone_number', 'like', "%{$query}%")
                                       ->orWhere('identifier', 'like', "%{$query}%");
                      });
                });

            // Apply permission-based filtering
            $conversationQuery = $this->permissionFilterService->filterConversations($conversationQuery, $user, $account);

            $conversations = $conversationQuery
                ->with(['contact', 'inbox'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $results['conversations'] = $conversations;
        }

        if ($type === 'all' || $type === 'contacts') {
            $contactQuery = Contact::where('account_id', $account->id)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%")
                      ->orWhere('phone_number', 'like', "%{$query}%")
                      ->orWhere('identifier', 'like', "%{$query}%");
                });

            // Apply permission-based filtering
            $contactQuery = $this->permissionFilterService->filterContacts($contactQuery, $user, $account);

            $contacts = $contactQuery
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $results['contacts'] = $contacts;
        }

        if ($type === 'all' || $type === 'messages') {
            $messageQuery = Message::whereHas('conversation', function ($q) use ($account) {
                    $q->where('account_id', $account->id);
                })
                ->where('content', 'like', "%{$query}%");

            // Apply permission-based filtering
            $messageQuery = $this->permissionFilterService->filterMessages($messageQuery, $user, $account);

            $messages = $messageQuery
                ->with(['conversation', 'sender'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $results['messages'] = $messages;
        }

        return response()->json(['data' => $results]);
    }

    /**
     * Search conversations.
     */
    public function conversations(Account $account, SearchRequest $request): JsonResponse
    {
        $user = $request->user();
        $query = $request->getQuery();

        $conversationQuery = Conversation::where('account_id', $account->id)
            ->where(function ($q) use ($query) {
                $q->where('display_id', 'like', "%{$query}%")
                  ->orWhereHas('contact', function ($contactQuery) use ($query) {
                      $contactQuery->where('name', 'like', "%{$query}%")
                                   ->orWhere('email', 'like', "%{$query}%")
                                   ->orWhere('phone_number', 'like', "%{$query}%")
                                   ->orWhere('identifier', 'like', "%{$query}%");
                  });
            });

        // Apply permission-based filtering
        $conversationQuery = $this->permissionFilterService->filterConversations($conversationQuery, $user, $account);

        $conversations = $conversationQuery
            ->with(['contact', 'inbox', 'assignee'])
            ->orderBy($request->getSortBy(), $request->getSortOrder())
            ->paginate($request->getPerPage());

        return response()->json(['data' => $conversations]);
    }

    /**
     * Search contacts.
     */
    public function contacts(Account $account, SearchRequest $request): JsonResponse
    {
        $user = $request->user();
        $query = $request->getQuery();

        $contactQuery = Contact::where('account_id', $account->id)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('phone_number', 'like', "%{$query}%")
                  ->orWhere('identifier', 'like', "%{$query}%");
            });

        // Apply permission-based filtering
        $contactQuery = $this->permissionFilterService->filterContacts($contactQuery, $user, $account);

        $contacts = $contactQuery
            ->orderBy($request->getSortBy(), $request->getSortOrder())
            ->paginate($request->getPerPage());

        return response()->json(['data' => $contacts]);
    }

    /**
     * Search messages.
     */
    public function messages(Account $account, SearchRequest $request): JsonResponse
    {
        $user = $request->user();
        $query = $request->getQuery();

        $messageQuery = Message::whereHas('conversation', function ($q) use ($account) {
                $q->where('account_id', $account->id);
            })
            ->where('content', 'like', "%{$query}%");

        // Apply permission-based filtering
        $messageQuery = $this->permissionFilterService->filterMessages($messageQuery, $user, $account);

        $messages = $messageQuery
            ->with(['conversation', 'sender'])
            ->orderBy($request->getSortBy(), $request->getSortOrder())
            ->paginate($request->getPerPage());

        return response()->json(['data' => $messages]);
    }
}
