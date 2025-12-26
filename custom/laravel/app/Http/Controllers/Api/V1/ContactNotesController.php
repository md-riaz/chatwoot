<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactNotesController extends Controller
{
    /**
     * Display a listing of notes for a contact.
     */
    public function index(Account $account, Contact $contact): JsonResource
    {
        abort_unless($contact->account_id === $account->id, 404);

        $notes = Note::where('contact_id', $contact->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate();

        return JsonResource::collection($notes);
    }

    /**
     * Store a newly created note.
     */
    public function store(Request $request, Account $account, Contact $contact): JsonResponse
    {
        abort_unless($contact->account_id === $account->id, 404);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $note = Note::create([
            ...$validated,
            'contact_id' => $contact->id,
            'account_id' => $account->id,
            'user_id' => auth()->id(),
        ]);

        return response()->json(['data' => $note->load('user')], 201);
    }

    /**
     * Display the specified note.
     */
    public function show(Account $account, Contact $contact, Note $note): JsonResponse
    {
        abort_unless($contact->account_id === $account->id, 404);
        abort_unless($note->contact_id === $contact->id, 404);

        return response()->json(['data' => $note->load('user')]);
    }

    /**
     * Update the specified note.
     */
    public function update(Request $request, Account $account, Contact $contact, Note $note): JsonResponse
    {
        abort_unless($contact->account_id === $account->id, 404);
        abort_unless($note->contact_id === $contact->id, 404);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $note->update($validated);

        return response()->json(['data' => $note]);
    }

    /**
     * Remove the specified note.
     */
    public function destroy(Account $account, Contact $contact, Note $note): JsonResponse
    {
        abort_unless($contact->account_id === $account->id, 404);
        abort_unless($note->contact_id === $contact->id, 404);

        $note->delete();

        return response()->json(null, 204);
    }
}
