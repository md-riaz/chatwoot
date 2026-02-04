<?php

namespace App\Observers;

use App\Models\Contact;
use App\Services\Contact\ContactSyncAttributesService;

/**
 * Contact Observer
 * 
 * Handles contact model events and ensures data consistency.
 * Matches Rails before_save :sync_contact_attributes callback.
 */
class ContactObserver
{
    /**
     * Handle the Contact "saving" event.
     * 
     * Called before create and update operations.
     * Matches Rails before_save callback.
     */
    public function saving(Contact $contact): void
    {
        // Sync attributes from additional_attributes to direct fields
        $syncService = new ContactSyncAttributesService($contact);
        $syncService->perform();
    }

    /**
     * Handle the Contact "created" event.
     */
    public function created(Contact $contact): void
    {
        // Additional logic after contact creation if needed
    }

    /**
     * Handle the Contact "updated" event.
     */
    public function updated(Contact $contact): void
    {
        // Additional logic after contact update if needed
    }

    /**
     * Handle the Contact "deleted" event.
     */
    public function deleted(Contact $contact): void
    {
        // Additional cleanup logic if needed
    }
}