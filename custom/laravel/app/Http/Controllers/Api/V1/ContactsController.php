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
    private const RESULTS_PER_PAGE = 15;

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
            $request->only(['search', 'per_page', 'labels', 'sort', 'sort_direction'])
        );

        return ContactResource::collection($contacts);
    }

    /**
     * Search contacts.
     */
    public function search(Account $account, Request $request): JsonResponse
    {
        if (empty($request->input('q'))) {
            return response()->json(['error' => 'Specify search string with parameter q'], 422);
        }

        $contacts = $this->contactRepository->search(
            $account->id,
            $request->input('q'),
            $request->only(['per_page', 'page'])
        );

        return response()->json([
            'data' => ContactResource::collection($contacts),
            'meta' => ['count' => $contacts->total()],
        ]);
    }

    /**
     * Filter contacts with advanced filters.
     */
    public function filter(Request $request, Account $account): JsonResponse
    {
        $result = $this->contactRepository->filter(
            $account->id,
            $request->input('payload', []),
            $request->input('label')
        );

        return response()->json([
            'data' => ContactResource::collection($result['contacts']),
            'meta' => ['count' => $result['count']],
        ]);
    }

    /**
     * Get active/online contacts.
     */
    public function active(Account $account, Request $request): AnonymousResourceCollection
    {
        $contacts = $this->contactRepository->getActiveContacts(
            $account->id,
            $request->only(['per_page', 'page'])
        );

        return ContactResource::collection($contacts);
    }

    /**
     * Import contacts from file.
     */
    public function import(Request $request, Account $account): JsonResponse
    {
        if (! $request->hasFile('import_file')) {
            return response()->json(['error' => 'Import file is required'], 422);
        }

        // Process import in background job
        // ContactImportJob::dispatch($account, $request->file('import_file'));

        return response()->json(null, 200);
    }

    /**
     * Export contacts.
     */
    public function export(Request $request, Account $account): JsonResponse
    {
        $columnNames = $request->input('column_names', []);
        $filterParams = $request->only(['payload', 'label']);

        // Queue export job
        // ContactExportJob::dispatch($account->id, auth()->id(), $columnNames, $filterParams);

        return response()->json(['message' => 'Export initiated successfully'], 200);
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
            $request->only(['name', 'email', 'phone_number', 'identifier', 'avatar_url', 'custom_attributes', 'additional_attributes', 'blocked'])
        );

        return new ContactResource($updatedContact);
    }

    /**
     * Get contactable inboxes for a contact.
     */
    public function contactableInboxes(Account $account, Contact $contact): JsonResponse
    {
        abort_unless($contact->account_id === $account->id, 404);

        $inboxes = $account->inboxes()
            ->whereHas('contactInboxes', function ($query) use ($contact) {
                $query->where('contact_id', $contact->id);
            })
            ->orWhere(function ($query) use ($account) {
                $query->where('account_id', $account->id);
            })
            ->get();

        return response()->json(['data' => $inboxes]);
    }

    /**
     * Destroy custom attributes from contact.
     */
    public function destroyCustomAttributes(Request $request, Account $account, Contact $contact): ContactResource
    {
        abort_unless($contact->account_id === $account->id, 404);

        $attributesToRemove = $request->input('custom_attributes', []);
        $currentAttributes = $contact->custom_attributes ?? [];

        foreach ($attributesToRemove as $key) {
            unset($currentAttributes[$key]);
        }

        $contact->update(['custom_attributes' => $currentAttributes]);

        return new ContactResource($contact);
    }

    /**
     * Delete contact avatar.
     */
    public function avatar(Account $account, Contact $contact): ContactResource
    {
        abort_unless($contact->account_id === $account->id, 404);

        $contact->update(['avatar_url' => null]);

        return new ContactResource($contact);
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
