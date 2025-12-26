<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CustomFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomFiltersController extends Controller
{
    /**
     * Display a listing of custom filters for an account.
     */
    public function index(Account $account, Request $request): JsonResource
    {
        $query = CustomFilter::where('account_id', $account->id);

        if ($request->has('filter_type')) {
            $query->where('filter_type', $request->filter_type);
        }

        return JsonResource::collection($query->paginate());
    }

    /**
     * Store a newly created custom filter.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'filter_type' => 'required|string|in:conversation,contact,report',
            'query' => 'required|array',
        ]);

        $filter = CustomFilter::create([
            ...$validated,
            'account_id' => $account->id,
            'user_id' => auth()->id(),
        ]);

        return response()->json(['data' => $filter], 201);
    }

    /**
     * Display the specified custom filter.
     */
    public function show(Account $account, CustomFilter $customFilter): JsonResponse
    {
        abort_unless($customFilter->account_id === $account->id, 404);

        return response()->json(['data' => $customFilter]);
    }

    /**
     * Update the specified custom filter.
     */
    public function update(Request $request, Account $account, CustomFilter $customFilter): JsonResponse
    {
        abort_unless($customFilter->account_id === $account->id, 404);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'filter_type' => 'string|in:conversation,contact,report',
            'query' => 'array',
        ]);

        $customFilter->update($validated);

        return response()->json(['data' => $customFilter]);
    }

    /**
     * Remove the specified custom filter.
     */
    public function destroy(Account $account, CustomFilter $customFilter): JsonResponse
    {
        abort_unless($customFilter->account_id === $account->id, 404);

        $customFilter->delete();

        return response()->json(null, 204);
    }
}
