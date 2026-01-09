<?php

namespace App\Services\Search;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class SearchService
{
    protected User $currentUser;
    protected Account $currentAccount;
    protected array $params;
    protected ?string $searchType;

    public function __construct(User $currentUser, Account $currentAccount, array $params, ?string $searchType = null)
    {
        $this->currentUser = $currentUser;
        $this->currentAccount = $currentAccount;
        $this->params = $params;
        $this->searchType = $searchType;
    }

    public function perform(): array
    {
        switch ($this->searchType) {
            case 'Message':
                return ['messages' => $this->filterMessages()];
            case 'Conversation':
                return ['conversations' => $this->filterConversations()];
            case 'Contact':
                return ['contacts' => $this->filterContacts()];
            case 'Article':
                return ['articles' => $this->filterArticles()];
            default:
                return [
                    'contacts' => $this->filterContacts(),
                    'messages' => $this->filterMessages(),
                    'conversations' => $this->filterConversations(),
                    'articles' => $this->filterArticles(),
                ];
        }
    }

    protected function getAccessibleInboxIds(): array
    {
        return $this->currentUser->assignedInboxes()->pluck('id')->toArray();
    }

    protected function getSearchQuery(): string
    {
        return trim($this->params['q'] ?? '');
    }

    protected function filterConversations()
    {
        $searchQuery = $this->getSearchQuery();
        $accessibleInboxIds = $this->getAccessibleInboxIds();

        return $this->currentAccount->conversations()
            ->whereIn('inbox_id', $accessibleInboxIds)
            ->join('contacts', 'conversations.contact_id', '=', 'contacts.id')
            ->where(function (Builder $query) use ($searchQuery) {
                $query->where('conversations.display_id', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('contacts.name', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('contacts.email', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('contacts.phone_number', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('contacts.identifier', 'LIKE', "%{$searchQuery}%");
            })
            ->orderBy('conversations.created_at', 'desc')
            ->paginate(15, ['*'], 'page', $this->params['page'] ?? 1);
    }

    protected function filterMessages()
    {
        $searchQuery = $this->getSearchQuery();
        $accessibleInboxIds = $this->getAccessibleInboxIds();

        return $this->currentAccount->messages()
            ->where('created_at', '>=', now()->subMonths(3))
            ->whereIn('inbox_id', $accessibleInboxIds)
            ->where('content', 'LIKE', "%{$searchQuery}%")
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'page', $this->params['page'] ?? 1);
    }

    protected function filterContacts()
    {
        $searchQuery = $this->getSearchQuery();

        return $this->currentAccount->contacts()
            ->where(function (Builder $query) use ($searchQuery) {
                $query->where('name', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('email', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('phone_number', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('identifier', 'LIKE', "%{$searchQuery}%");
            })
            ->orderBy('last_activity_at', 'desc')
            ->paginate(15, ['*'], 'page', $this->params['page'] ?? 1);
    }

    protected function filterArticles()
    {
        $searchQuery = $this->getSearchQuery();

        return $this->currentAccount->articles()
            ->where(function (Builder $query) use ($searchQuery) {
                $query->where('title', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('content', 'LIKE', "%{$searchQuery}%");
            })
            ->paginate(15, ['*'], 'page', $this->params['page'] ?? 1);
    }
}