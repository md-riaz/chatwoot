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
    private const DEFAULT_FILTER_TYPE = 'conversation';

    /**
     * Display a listing of custom filters for an account (scoped to current user).
     */
    public function index(Account $account, Request $request): JsonResource
    {
        $filterType = $request->input('filter_type', self::DEFAULT_FILTER_TYPE);

        $query = CustomFilter::where('account_id', $account->id)
            ->where('user_id', auth()->id())
            ->where('filter_type', $filterType);

        return JsonResource::collection($query->get());
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
            'name' => $validated['name'],
            'filter_type' => $validated['filter_type'],
            'query' => $validated['query'],
            'account_id' => $account->id,
            'user_id' => auth()->id(),
        ]);

        return response()->json(['data' => $filter], 201);
    }

    /**
     * Display the specified custom filter (only if owned by current user).
     */
    public function show(Account $account, CustomFilter $customFilter): JsonResponse
    {
        abort_unless($customFilter->account_id === $account->id, 404);
        abort_unless($customFilter->user_id === auth()->id(), 404);

        return response()->json(['data' => $customFilter]);
    }

    /**
     * Update the specified custom filter (only if owned by current user).
     */
    public function update(Request $request, Account $account, CustomFilter $customFilter): JsonResponse
    {
        abort_unless($customFilter->account_id === $account->id, 404);
        abort_unless($customFilter->user_id === auth()->id(), 404);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'filter_type' => 'string|in:conversation,contact,report',
            'query' => 'array',
        ]);

        $customFilter->update($validated);

        return response()->json(['data' => $customFilter]);
    }

    /**
     * Remove the specified custom filter (only if owned by current user).
     */
    public function destroy(Account $account, CustomFilter $customFilter): JsonResponse
    {
        abort_unless($customFilter->account_id === $account->id, 404);
        abort_unless($customFilter->user_id === auth()->id(), 404);

        $customFilter->delete();

        return response()->json(null, 204);
    }
}
