<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Contact\CreateContactAction;
use App\Actions\Contact\MergeContactsAction;
use App\Actions\Contact\UpdateContactAction;
use App\Actions\DataImport\GetImportStatusAction;
use App\Actions\DataImport\StartDataImportAction;
use App\Data\Contact\ContactData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\StoreContactRequest;
use App\Http\Resources\Contact\ContactResource;
use App\Models\Account;
use App\Models\Contact;
use App\Repositories\Contact\ContactRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

        $file = $request->file('import_file');
        $path = $file->store('imports');

        // Optional mapping and duplicate handling
        $mapping = $request->input('mapping', []); // e.g. {"csv_name":"name","csv_email":"email"}
        $duplicateHandling = $request->input('duplicate_handling', 'skip'); // skip|update|create_duplicate

        $importResult = StartDataImportAction::run($account, (int) auth()->id(), $path, $mapping, $duplicateHandling);

        return response()->json([
            'message' => 'Import queued',
            'import_id' => $importResult['import_id'],
            'data_import_id' => $importResult['data_import_id'],
        ], 202);
    }

    /**
     * Return import status for a given import id.
     */
    public function importStatus(Account $account, string $importId): JsonResponse
    {
        $status = GetImportStatusAction::run($importId);

        if (! $status) {
            return response()->json(['error' => 'not_found'], 404);
        }

        return response()->json(['data' => $status]);
    }

    /**
     * Export contacts.
     */
    public function export(Request $request, Account $account): JsonResponse
    {
        $columnNames = $request->input('column_names', []);
        $filterParams = $request->only(['payload', 'label']);

        // Queue export job
        \App\Jobs\ExportContactsJob::dispatch($account->id, auth()->id(), $columnNames, $filterParams);

        return response()->json(['message' => 'Export queued'], 202);
    }

    /**
     * Securely download the latest export for the authenticated user.
     * Uses a cached key set by the ExportContactsJob.
     */
    public function downloadExport(Account $account)
    {
        $userId = auth()->id();
        $cacheKey = 'export_result:' . $userId;
        $path = Cache::get($cacheKey);

        if (! $path || ! \Illuminate\Support\Facades\Storage::exists($path)) {
            return response()->json(['error' => 'not_found'], 404);
        }

        return \Illuminate\Support\Facades\Storage::download($path);
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

        // Get inboxes that the contact already has contact_inboxes for
        $existingInboxIds = $contact->contactInboxes()->pluck('inbox_id')->toArray();

        // Get all inboxes for the account that are contactable
        $inboxes = $account->inboxes()
            ->where(function ($query) use ($existingInboxIds) {
                $query->whereIn('id', $existingInboxIds)
                    ->orWhere('channel_type', 'like', '%WebWidget%')
                    ->orWhere('channel_type', 'like', '%Api%');
            })
            ->get()
            ->map(function ($inbox) use ($existingInboxIds) {
                return [
                    'inbox' => $inbox,
                    'source_id' => in_array($inbox->id, $existingInboxIds)
                        ? $inbox->contactInboxes()->where('contact_id', request()->route('contact'))->first()?->source_id
                        : null,
                ];
            });

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
