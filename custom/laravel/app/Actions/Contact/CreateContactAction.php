<?php

namespace App\Actions\Contact;

use App\Data\Contact\ContactData;
use App\Models\Contact;
use App\Repositories\Contact\ContactRepository;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateContactAction
{
    use AsAction;

    public function __construct(
        private ContactRepository $contactRepository
    ) {}

    public function handle(ContactData $data): Contact
    {
        $contact = $this->contactRepository->create($data->toArray());

        // Trigger event
        // event(new ContactCreated($contact));

        return $contact;
    }

    public function rules(): array
    {
        return ContactData::rules();
    }
}
