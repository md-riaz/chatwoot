<?php

namespace App\Http\Controllers\Api\V1\Public\Inboxes;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactInbox;
use App\Models\Inbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContactsController extends Controller
{
    /**
     * Create a new contact for an inbox.
     * POST /api/v1/public/inboxes/{inbox}/contacts
     */
    public function store(Request $request, Inbox $inbox): JsonResponse
    {
        $validated = $request->validate([
            'identifier' => 'nullable|string',
            'identifier_hash' => 'nullable|string',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone_number' => 'nullable|string|max:50',
            'avatar_url' => 'nullable|url',
            'custom_attributes' => 'nullable|array',
        ]);

        // Check if contact with identifier already exists
        if (!empty($validated['identifier'])) {
            $existingContact = Contact::where('account_id', $inbox->account_id)
                ->where('identifier', $validated['identifier'])
                ->first();

            if ($existingContact) {
                // Get or create contact inbox
                $contactInbox = ContactInbox::firstOrCreate(
                    [
                        'contact_id' => $existingContact->id,
                        'inbox_id' => $inbox->id,
                    ],
                    [
                        'source_id' => Str::uuid()->toString(),
                    ]
                );

                return response()->json([
                    'id' => $existingContact->id,
                    'source_id' => $contactInbox->source_id,
                    'pubsub_token' => $contactInbox->source_id,
                ]);
            }
        }

        // Create new contact
        $contact = Contact::create([
            'account_id' => $inbox->account_id,
            'name' => $validated['name'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'identifier' => $validated['identifier'] ?? null,
            'avatar_url' => $validated['avatar_url'] ?? null,
            'custom_attributes' => $validated['custom_attributes'] ?? [],
        ]);

        // Create contact inbox
        $contactInbox = ContactInbox::create([
            'contact_id' => $contact->id,
            'inbox_id' => $inbox->id,
            'source_id' => Str::uuid()->toString(),
        ]);

        return response()->json([
            'id' => $contact->id,
            'source_id' => $contactInbox->source_id,
            'pubsub_token' => $contactInbox->source_id,
        ], 201);
    }

    /**
     * Display a contact.
     * GET /api/v1/public/inboxes/{inbox}/contacts/{contact}
     */
    public function show(Inbox $inbox, Contact $contact): JsonResponse
    {
        $contactInbox = ContactInbox::where('contact_id', $contact->id)
            ->where('inbox_id', $inbox->id)
            ->first();

        if (!$contactInbox) {
            return response()->json(['error' => 'Contact not found'], 404);
        }

        return response()->json([
            'id' => $contact->id,
            'name' => $contact->name,
            'email' => $contact->email,
            'phone_number' => $contact->phone_number,
            'identifier' => $contact->identifier,
            'custom_attributes' => $contact->custom_attributes ?? [],
            'source_id' => $contactInbox->source_id,
            'pubsub_token' => $contactInbox->source_id,
        ]);
    }

    /**
     * Update a contact.
     * PATCH /api/v1/public/inboxes/{inbox}/contacts/{contact}
     */
    public function update(Request $request, Inbox $inbox, Contact $contact): JsonResponse
    {
        $contactInbox = ContactInbox::where('contact_id', $contact->id)
            ->where('inbox_id', $inbox->id)
            ->first();

        if (!$contactInbox) {
            return response()->json(['error' => 'Contact not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone_number' => 'nullable|string|max:50',
            'avatar_url' => 'nullable|url',
            'custom_attributes' => 'nullable|array',
        ]);

        // Merge custom attributes
        if (isset($validated['custom_attributes'])) {
            $validated['custom_attributes'] = array_merge(
                $contact->custom_attributes ?? [],
                $validated['custom_attributes']
            );
        }

        $contact->update(array_filter($validated));

        return response()->json([
            'id' => $contact->id,
            'name' => $contact->name,
            'email' => $contact->email,
            'phone_number' => $contact->phone_number,
            'identifier' => $contact->identifier,
            'custom_attributes' => $contact->custom_attributes ?? [],
            'source_id' => $contactInbox->source_id,
        ]);
    }
}
