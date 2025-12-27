<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CustomRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomRolesController extends Controller
{
    /**
     * Display a listing of custom roles for an account.
     */
    public function index(Account $account): JsonResource
    {
        $customRoles = CustomRole::where('account_id', $account->id)->paginate();

        return JsonResource::collection($customRoles);
    }

    /**
     * Store a newly created custom role.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:custom_roles,name,NULL,id,account_id,' . $account->id,
            'description' => 'nullable|string',
            'permissions' => 'required|array',
            'permissions.*' => 'string',
        ]);

        $customRole = CustomRole::create([
            ...$validated,
            'account_id' => $account->id,
        ]);

        return response()->json(['data' => $customRole], 201);
    }

    /**
     * Display the specified custom role.
     */
    public function show(Account $account, CustomRole $customRole): JsonResponse
    {
        abort_unless($customRole->account_id === $account->id, 404);

        return response()->json(['data' => $customRole]);
    }

    /**
     * Update the specified custom role.
     */
    public function update(Request $request, Account $account, CustomRole $customRole): JsonResponse
    {
        abort_unless($customRole->account_id === $account->id, 404);

        $validated = $request->validate([
            'name' => 'string|max:255|unique:custom_roles,name,' . $customRole->id . ',id,account_id,' . $account->id,
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'string',
        ]);

        $customRole->update($validated);

        return response()->json(['data' => $customRole]);
    }

    /**
     * Remove the specified custom role.
     */
    public function destroy(Account $account, CustomRole $customRole): JsonResponse
    {
        abort_unless($customRole->account_id === $account->id, 404);

        $customRole->delete();

        return response()->json(null, 204);
    }
}
