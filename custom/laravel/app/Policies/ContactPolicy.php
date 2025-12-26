<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;

class ContactPolicy
{
    /**
     * Determine if the user can view any contacts.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the contact.
     */
    public function view(User $user, Contact $contact): bool
    {
        return $user->accounts()->where('account_id', $contact->account_id)->exists();
    }

    /**
     * Determine if the user can create contacts.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the contact.
     */
    public function update(User $user, Contact $contact): bool
    {
        return $user->accounts()->where('account_id', $contact->account_id)->exists();
    }

    /**
     * Determine if the user can delete the contact.
     */
    public function delete(User $user, Contact $contact): bool
    {
        return $user->accounts()->where('account_id', $contact->account_id)->exists();
    }

    /**
     * Determine if the user can merge contacts.
     */
    public function merge(User $user, Contact $contact): bool
    {
        return $user->accounts()->where('account_id', $contact->account_id)->exists();
    }
}
