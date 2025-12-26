<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Contact\CreateContactAction;
use App\Actions\Contact\MergeContactsAction;
use App\Actions\Contact\UpdateContactAction;
use App\Data\Contact\ContactData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\StoreContactRequest;
use App\Http\Resources\Contact\ContactResource;
use App\Models\Account;
use App\Models\Contact;
use App\Repositories\Contact\ContactRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactsController extends Controller
{
    public function __construct(
        private ContactRepository $contactRepository
    ) {}

    /**
     * Display a listing of contacts for an account.
     */
    public function index(Account $account, Request $request): AnonymousResourceCollection
    {
        $contacts = $this->contactRepository->findForAccount(
            $account->id,
            $request->only(['search', 'per_page'])
        );

        return ContactResource::collection($contacts);
    }

    /**
     * Store a newly created contact.
     */
    public function store(StoreContactRequest $request, Account $account): ContactResource
    {
        $data = array_merge($request->validated(), ['account_id' => $account->id]);

        $contact = CreateContactAction::run(ContactData::from($data));

        return new ContactResource($contact);
    }

    /**
     * Display the specified contact.
     */
    public function show(Account $account, Contact $contact): ContactResource
    {
        abort_unless($contact->account_id === $account->id, 404);

        return new ContactResource($contact->loadCount('conversations'));
    }

    /**
     * Update the specified contact.
     */
    public function update(Request $request, Account $account, Contact $contact): ContactResource
    {
        abort_unless($contact->account_id === $account->id, 404);

        $updatedContact = UpdateContactAction::run(
            $contact,
            $request->only(['name', 'email', 'phone_number', 'identifier', 'avatar_url', 'custom_attributes', 'additional_attributes'])
        );

        return new ContactResource($updatedContact);
    }

    /**
     * Merge two contacts.
     */
    public function merge(Request $request, Account $account, Contact $contact): ContactResource
    {
        abort_unless($contact->account_id === $account->id, 404);

        $sourceContact = Contact::where('account_id', $account->id)
            ->findOrFail($request->source_contact_id);

        $mergedContact = MergeContactsAction::run($contact, $sourceContact);

        return new ContactResource($mergedContact);
    }

    /**
     * Remove the specified contact.
     */
    public function destroy(Account $account, Contact $contact): JsonResponse
    {
        abort_unless($contact->account_id === $account->id, 404);

        $contact->delete();

        return response()->json(null, 204);
    }
}
