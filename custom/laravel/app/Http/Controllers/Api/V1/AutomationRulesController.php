<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AutomationRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AutomationRulesController extends Controller
{
    /**
     * Display a listing of automation rules for an account.
     */
    public function index(Account $account): JsonResource
    {
        $rules = AutomationRule::where('account_id', $account->id)->paginate();

        return JsonResource::collection($rules);
    }

    /**
     * Store a newly created automation rule.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_name' => 'required|string',
            'conditions' => 'nullable|array',
            'actions' => 'required|array',
            'active' => 'boolean',
        ]);

        $rule = AutomationRule::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'event_name' => $validated['event_name'],
            'conditions' => $validated['conditions'] ?? [],
            'actions' => $validated['actions'],
            'active' => $validated['active'] ?? true,
            'account_id' => $account->id,
        ]);

        return response()->json(['data' => $rule], 201);
    }

    /**
     * Display the specified automation rule.
     */
    public function show(Account $account, AutomationRule $automationRule): JsonResponse
    {
        abort_unless($automationRule->account_id === $account->id, 404);

        return response()->json(['data' => $automationRule]);
    }

    /**
     * Update the specified automation rule.
     */
    public function update(Request $request, Account $account, AutomationRule $automationRule): JsonResponse
    {
        abort_unless($automationRule->account_id === $account->id, 404);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'event_name' => 'string',
            'conditions' => 'array',
            'actions' => 'array',
            'active' => 'boolean',
        ]);

        $automationRule->update($validated);

        return response()->json(['data' => $automationRule]);
    }

    /**
     * Remove the specified automation rule.
     */
    public function destroy(Account $account, AutomationRule $automationRule): JsonResponse
    {
        abort_unless($automationRule->account_id === $account->id, 404);

        $automationRule->delete();

        return response()->json(null, 204);
    }

    /**
     * Clone an existing automation rule.
     */
    public function clone(Account $account, AutomationRule $automationRule): JsonResponse
    {
        abort_unless($automationRule->account_id === $account->id, 404);

        $clonedRule = $automationRule->replicate();
        $clonedRule->name = $automationRule->name . ' (Copy)';
        $clonedRule->save();

        return response()->json(['data' => $clonedRule], 201);
    }
}
