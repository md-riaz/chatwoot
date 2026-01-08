<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Macro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MacrosController extends Controller
{
    /**
     * Display a listing of macros for an account.
     */
    public function index(Account $account): JsonResource
    {
        $macros = Macro::where('account_id', $account->id)->paginate();

        return JsonResource::collection($macros);
    }

    /**
     * Store a newly created macro.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'actions' => 'required|array',
            'visibility' => 'string|in:global,personal',
        ]);

        $macro = Macro::create([
            ...$validated,
            'account_id' => $account->id,
            'created_by_id' => auth()->id(),
            'updated_by_id' => auth()->id(),
        ]);

        return response()->json(['data' => $macro], 201);
    }

    /**
     * Display the specified macro.
     */
    public function show(Account $account, Macro $macro): JsonResponse
    {
        abort_unless($macro->account_id === $account->id, 404);

        return response()->json(['data' => $macro]);
    }

    /**
     * Update the specified macro.
     */
    public function update(Request $request, Account $account, Macro $macro): JsonResponse
    {
        abort_unless($macro->account_id === $account->id, 404);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'actions' => 'array',
            'visibility' => 'string|in:global,personal',
        ]);

        $macro->update([
            ...$validated,
            'updated_by_id' => auth()->id(),
        ]);

        return response()->json(['data' => $macro]);
    }

    /**
     * Remove the specified macro.
     */
    public function destroy(Account $account, Macro $macro): JsonResponse
    {
        abort_unless($macro->account_id === $account->id, 404);

        $macro->delete();

        return response()->json(null, 204);
    }

    /**
     * Execute a macro on a conversation.
     */
    public function execute(Request $request, Account $account, Macro $macro): JsonResponse
    {
        abort_unless($macro->account_id === $account->id, 404);

        $validated = $request->validate([
            'conversation_ids' => 'required|array',
            'conversation_ids.*' => 'exists:conversations,id',
        ]);

        // Execute macro actions on conversations
        // This would typically be handled by a service
        foreach ($validated['conversation_ids'] as $conversationId) {
            // Apply macro actions to conversation
            // MacroExecutionService::execute($macro, $conversationId);
        }

        return response()->json(['message' => 'Macro executed successfully']);
    }
}
