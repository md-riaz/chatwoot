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
     * Map filter type input (string or int) to internal integer constant.
     */
    private function mapFilterTypeToInt($value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        switch (strtolower((string) $value)) {
            case 'conversation':
                return CustomFilter::TYPE_CONVERSATION;
            case 'contact':
                return CustomFilter::TYPE_CONTACT;
            case 'report':
                return CustomFilter::TYPE_REPORT;
            default:
                return CustomFilter::TYPE_CONVERSATION;
        }
    }

    /**
     * Display a listing of custom filters for an account (scoped to current user).
     */
    public function index(Account $account, Request $request): JsonResource
    {
        $filterTypeInput = $request->input('filter_type', $request->input('filterType', self::DEFAULT_FILTER_TYPE));
        $filterType = $this->mapFilterTypeToInt($filterTypeInput);

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
            'filter_type' => 'required',
            'query' => 'required|array',
        ]);

        $filterType = $this->mapFilterTypeToInt($validated['filter_type']);

        $filter = CustomFilter::create([
            'name' => $validated['name'],
            'filter_type' => $filterType,
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
            'filter_type' => '',
            'query' => 'array',
        ]);

        // Map filter_type if provided
        if (array_key_exists('filter_type', $validated)) {
            $validated['filter_type'] = $this->mapFilterTypeToInt($validated['filter_type']);
        }

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
