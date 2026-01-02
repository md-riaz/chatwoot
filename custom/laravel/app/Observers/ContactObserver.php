<?php

namespace App\Observers;

use App\Models\Contact;

class ContactObserver
{
    /**
     * Handle the Contact "created" event.
     */
    public function created(Contact $contact): void
    {
        if ($contact->company_id) {
            $contact->company->updateContactsCount();
        }
    }

    /**
     * Handle the Contact "updated" event.
     */
    public function updated(Contact $contact): void
    {
        // If company_id changed, update both old and new company counts
        if ($contact->isDirty('company_id')) {
            $originalCompanyId = $contact->getOriginal('company_id');
            
            // Update old company count
            if ($originalCompanyId) {
                $oldCompany = \App\Models\Company::find($originalCompanyId);
                if ($oldCompany) {
                    $oldCompany->updateContactsCount();
                }
            }
            
            // Update new company count
            if ($contact->company_id) {
                $contact->company->updateContactsCount();
            }
        }
    }

    /**
     * Handle the Contact "deleted" event.
     */
    public function deleted(Contact $contact): void
    {
        if ($contact->company_id) {
            $contact->company->updateContactsCount();
        }
    }
}