<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Search across conversations, contacts, and messages.
     */
    public function index(Account $account, Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all'); // all, conversations, contacts, messages

        $results = [];

        if ($type === 'all' || $type === 'conversations') {
            $conversations = Conversation::where('account_id', $account->id)
                ->where(function ($q) use ($query) {
                    $q->where('display_id', 'like', "%{$query}%")
                      ->orWhereHas('contact', function ($contactQuery) use ($query) {
                          $contactQuery->where('name', 'like', "%{$query}%")
                                       ->orWhere('email', 'like', "%{$query}%");
                      });
                })
                ->limit(10)
                ->get();

            $results['conversations'] = $conversations;
        }

        if ($type === 'all' || $type === 'contacts') {
            $contacts = Contact::where('account_id', $account->id)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%")
                      ->orWhere('phone_number', 'like', "%{$query}%")
                      ->orWhere('identifier', 'like', "%{$query}%");
                })
                ->limit(10)
                ->get();

            $results['contacts'] = $contacts;
        }

        if ($type === 'all' || $type === 'messages') {
            $messages = Message::whereHas('conversation', function ($q) use ($account) {
                    $q->where('account_id', $account->id);
                })
                ->where('content', 'like', "%{$query}%")
                ->limit(10)
                ->get();

            $results['messages'] = $messages;
        }

        return response()->json(['data' => $results]);
    }

    /**
     * Search conversations.
     */
    public function conversations(Account $account, Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        $conversations = Conversation::where('account_id', $account->id)
            ->where(function ($q) use ($query) {
                $q->where('display_id', 'like', "%{$query}%")
                  ->orWhereHas('contact', function ($contactQuery) use ($query) {
                      $contactQuery->where('name', 'like', "%{$query}%")
                                   ->orWhere('email', 'like', "%{$query}%");
                  });
            })
            ->paginate();

        return response()->json(['data' => $conversations]);
    }

    /**
     * Search contacts.
     */
    public function contacts(Account $account, Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        $contacts = Contact::where('account_id', $account->id)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('phone_number', 'like', "%{$query}%");
            })
            ->paginate();

        return response()->json(['data' => $contacts]);
    }

    /**
     * Search messages.
     */
    public function messages(Account $account, Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        $messages = Message::whereHas('conversation', function ($q) use ($account) {
                $q->where('account_id', $account->id);
            })
            ->where('content', 'like', "%{$query}%")
            ->with(['conversation', 'sender'])
            ->paginate();

        return response()->json(['data' => $messages]);
    }
}
