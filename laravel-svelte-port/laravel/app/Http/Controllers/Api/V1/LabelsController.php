<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Label;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabelsController extends Controller
{
    /**
     * Display a listing of labels for an account.
     */
    public function index(Account $account): JsonResource
    {
        $labels = Label::where('account_id', $account->id)->paginate();

        return JsonResource::collection($labels);
    }

    /**
     * Store a newly created label.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'show_on_sidebar' => 'boolean',
        ]);

        $label = Label::create([
            ...$validated,
            'account_id' => $account->id,
        ]);

        return response()->json(['data' => $label], 201);
    }

    /**
     * Display the specified label.
     */
    public function show(Account $account, Label $label): JsonResponse
    {
        abort_unless($label->account_id === $account->id, 404);

        return response()->json(['data' => $label]);
    }

    /**
     * Update the specified label.
     */
    public function update(Request $request, Account $account, Label $label): JsonResponse
    {
        abort_unless($label->account_id === $account->id, 404);

        $validated = $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'show_on_sidebar' => 'boolean',
        ]);

        $label->update($validated);

        return response()->json(['data' => $label]);
    }

    /**
     * Remove the specified label.
     */
    public function destroy(Account $account, Label $label): JsonResponse
    {
        abort_unless($label->account_id === $account->id, 404);

        $label->delete();

        return response()->json(null, 204);
    }
}
