<?php

namespace App\Http\Controllers\Api\V1\Widget;

use App\Models\Contact;
use App\Models\ContactInbox;
use App\Models\Inbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContactsController extends BaseController
{
    /**
     * Get the current contact for the widget.
     * GET /api/v1/widget/contact
     */
    public function show(Request $request): JsonResponse
    {
        $contact = $this->getContactFromToken($request);

        if (!$contact) {
            return response()->json(['error' => 'Contact not found'], 404);
        }

        return response()->json([
            'id' => $contact->id,
            'email' => $contact->email,
            'name' => $contact->name,
            'phone_number' => $contact->phone_number,
            'avatar_url' => $contact->avatar_url,
            'identifier' => $contact->identifier,
            'custom_attributes' => $contact->custom_attributes ?? [],
        ]);
    }

    /**
     * Update the contact.
     * PATCH /api/v1/widget/contact
     */
    public function update(Request $request): JsonResponse
    {
        $contact = $this->getContactFromToken($request);

        if (!$contact) {
            return response()->json(['error' => 'Contact not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
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
            'email' => $contact->email,
            'name' => $contact->name,
            'phone_number' => $contact->phone_number,
            'avatar_url' => $contact->avatar_url,
            'identifier' => $contact->identifier,
            'custom_attributes' => $contact->custom_attributes ?? [],
        ]);
    }

    /**
     * Destroy custom attributes.
     * POST /api/v1/widget/contact/destroy_custom_attributes
     */
    public function destroyCustomAttributes(Request $request): JsonResponse
    {
        $contact = $this->getContactFromToken($request);

        if (!$contact) {
            return response()->json(['error' => 'Contact not found'], 404);
        }

        $validated = $request->validate([
            'custom_attributes' => 'required|array',
        ]);

        $customAttributes = $contact->custom_attributes ?? [];

        foreach ($validated['custom_attributes'] as $key) {
            unset($customAttributes[$key]);
        }

        $contact->update(['custom_attributes' => $customAttributes]);

        return response()->json(['success' => true]);
    }

    /**
     * Set user identifier for contact.
     * PATCH /api/v1/widget/contact/set_user
     */
    public function setUser(Request $request): JsonResponse
    {
        $contactInbox = $this->resolveContactInbox($request);

        if (!$contactInbox) {
            return response()->json(['error' => 'Contact not found'], 404);
        }

        $validated = $request->validate([
            'identifier' => 'required|string',
            'identifier_hash' => 'nullable|string',
            'email' => 'nullable|email',
            'name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:50',
            'avatar_url' => 'nullable|url',
            'custom_attributes' => 'nullable|array',
        ]);

        $contact = $contactInbox->contact;
        $account = $contactInbox->inbox->account;

        // Check if there's an existing contact with this identifier
        $existingContact = Contact::where('account_id', $account->id)
            ->where('identifier', $validated['identifier'])
            ->where('id', '!=', $contact->id)
            ->first();

        if ($existingContact) {
            // Link the contact inbox to the existing contact
            $contactInbox->update(['contact_id' => $existingContact->id]);
            $contact = $existingContact;
        } else {
            // Update the current contact with the identifier
            $updateData = ['identifier' => $validated['identifier']];

            if (isset($validated['email'])) {
                $updateData['email'] = $validated['email'];
            }
            if (isset($validated['name'])) {
                $updateData['name'] = $validated['name'];
            }
            if (isset($validated['phone_number'])) {
                $updateData['phone_number'] = $validated['phone_number'];
            }
            if (isset($validated['avatar_url'])) {
                $updateData['avatar_url'] = $validated['avatar_url'];
            }
            if (isset($validated['custom_attributes'])) {
                $updateData['custom_attributes'] = array_merge(
                    $contact->custom_attributes ?? [],
                    $validated['custom_attributes']
                );
            }

            $contact->update($updateData);
        }

        return response()->json([
            'id' => $contact->id,
            'email' => $contact->email,
            'name' => $contact->name,
            'phone_number' => $contact->phone_number,
            'avatar_url' => $contact->avatar_url,
            'identifier' => $contact->identifier,
            'custom_attributes' => $contact->custom_attributes ?? [],
            'pubsub_token' => $contactInbox->source_id,
        ]);
    }
}
