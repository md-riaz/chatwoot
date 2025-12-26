<?php

namespace App\Actions\Contact;

use App\Models\Contact;
use App\Repositories\Contact\ContactRepository;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class MergeContactsAction
{
    use AsAction;

    public function __construct(
        private ContactRepository $contactRepository
    ) {}

    /**
     * Merge source contact into target contact.
     */
    public function handle(Contact $targetContact, Contact $sourceContact): Contact
    {
        return DB::transaction(function () use ($targetContact, $sourceContact) {
            // Transfer conversations
            $sourceContact->conversations()->update([
                'contact_id' => $targetContact->id,
            ]);

            // Transfer contact inboxes
            $sourceContact->contactInboxes()->update([
                'contact_id' => $targetContact->id,
            ]);

            // Merge custom attributes
            $mergedAttributes = array_merge(
                $sourceContact->custom_attributes ?? [],
                $targetContact->custom_attributes ?? []
            );
            $targetContact->update(['custom_attributes' => $mergedAttributes]);

            // Delete source contact
            $sourceContact->delete();

            // Trigger event
            // event(new ContactsMerged($targetContact, $sourceContact));

            return $targetContact->fresh();
        });
    }
}
