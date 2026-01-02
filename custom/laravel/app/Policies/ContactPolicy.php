<?php

namespace App\Policies;

use App\Models\AccountUser;
use App\Models\Contact;
use App\Models\User;

class ContactPolicy
{
    /**
     * Determine if the user can view any contacts.
     */
    public function viewAny(User $user, $accountId): bool
    {
        $accountUser = $this->getAccountUser($user, $accountId);
        if (!$accountUser) {
            return false;
        }

        return $accountUser->hasPermission('contact_manage');
    }

    /**
     * Determine if the user can view the contact.
     */
    public function view(User $user, Contact $contact): bool
    {
        $accountUser = $this->getAccountUser($user, $contact->account_id);
        if (!$accountUser) {
            return false;
        }

        return $accountUser->hasPermission('contact_manage');
    }

    /**
     * Determine if the user can create contacts.
     */
    public function create(User $user, $accountId): bool
    {
        $accountUser = $this->getAccountUser($user, $accountId);
        if (!$accountUser) {
            return false;
        }

        return $accountUser->hasPermission('contact_manage');
    }

    /**
     * Determine if the user can update the contact.
     */
    public function update(User $user, Contact $contact): bool
    {
        return $this->view($user, $contact);
    }

    /**
     * Determine if the user can delete the contact.
     */
    public function delete(User $user, Contact $contact): bool
    {
        return $this->view($user, $contact);
    }

    /**
     * Get the account user relationship
     */
    private function getAccountUser(User $user, int $accountId): ?AccountUser
    {
        return $user->accountUsers()->where('account_id', $accountId)->first();
    }
}