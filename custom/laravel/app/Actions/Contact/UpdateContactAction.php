<?php

namespace App\Actions\Contact;

use App\Models\Contact;
use App\Repositories\Contact\ContactRepository;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateContactAction
{
    use AsAction;

    public function __construct(
        private ContactRepository $contactRepository
    ) {}

    public function handle(Contact $contact, array $data): Contact
    {
        $this->contactRepository->update($contact->id, $data);

        // Trigger event
        // event(new ContactUpdated($contact));

        return $contact->fresh();
    }
}
