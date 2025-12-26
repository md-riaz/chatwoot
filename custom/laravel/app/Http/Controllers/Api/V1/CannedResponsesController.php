<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CannedResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CannedResponsesController extends Controller
{
    /**
     * Display a listing of canned responses for an account.
     */
    public function index(Account $account, Request $request): JsonResource
    {
        $query = CannedResponse::where('account_id', $account->id);

        if ($request->has('search')) {
            $query->where('short_code', 'like', "%{$request->search}%");
        }

        return JsonResource::collection($query->paginate());
    }

    /**
     * Store a newly created canned response.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'short_code' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $cannedResponse = CannedResponse::create([
            ...$validated,
            'account_id' => $account->id,
        ]);

        return response()->json(['data' => $cannedResponse], 201);
    }

    /**
     * Display the specified canned response.
     */
    public function show(Account $account, CannedResponse $cannedResponse): JsonResponse
    {
        abort_unless($cannedResponse->account_id === $account->id, 404);

        return response()->json(['data' => $cannedResponse]);
    }

    /**
     * Update the specified canned response.
     */
    public function update(Request $request, Account $account, CannedResponse $cannedResponse): JsonResponse
    {
        abort_unless($cannedResponse->account_id === $account->id, 404);

        $validated = $request->validate([
            'short_code' => 'string|max:255',
            'content' => 'string',
        ]);

        $cannedResponse->update($validated);

        return response()->json(['data' => $cannedResponse]);
    }

    /**
     * Remove the specified canned response.
     */
    public function destroy(Account $account, CannedResponse $cannedResponse): JsonResponse
    {
        abort_unless($cannedResponse->account_id === $account->id, 404);

        $cannedResponse->delete();

        return response()->json(null, 204);
    }
}
