<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CustomRole;
use App\Http\Resources\CustomRoleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;

class CustomRolesController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage_custom_roles')->only(['store', 'update', 'destroy']);
    }

    /**
     * Display a listing of custom roles for an account.
     */
    public function index(Account $account): JsonResource
    {
        $customRoles = CustomRole::where('account_id', $account->id)->paginate();

        return CustomRoleResource::collection($customRoles);
    }

    /**
     * Store a newly created custom role.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:custom_roles,name,NULL,id,account_id,' . $account->id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'required|array|min:1',
            'permissions.*' => [
                'string',
                Rule::in(CustomRole::PERMISSIONS)
            ],
        ]);

        $customRole = CustomRole::create(array_merge($validated, ['account_id' => $account->id]));

        return (new CustomRoleResource($customRole))->response()->setStatusCode(201);
    }

    /**
     * Display the specified custom role.
     */
    public function show(Account $account, CustomRole $customRole): JsonResponse
    {
        abort_unless($customRole->account_id === $account->id, 404);

        return new CustomRoleResource($customRole);
    }

    /**
     * Update the specified custom role.
     */
    public function update(Request $request, Account $account, CustomRole $customRole): JsonResponse
    {
        abort_unless($customRole->account_id === $account->id, 404);

        $validated = $request->validate([
            'name' => 'string|max:255|unique:custom_roles,name,' . $customRole->id . ',id,account_id,' . $account->id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'array|min:1',
            'permissions.*' => [
                'string',
                Rule::in(CustomRole::PERMISSIONS)
            ],
        ]);

        $customRole->update($validated);

        return new CustomRoleResource($customRole);
    }

    /**
     * Remove the specified custom role.
     */
    public function destroy(Account $account, CustomRole $customRole): JsonResponse
    {
        abort_unless($customRole->account_id === $account->id, 404);

        // Check if any account users are using this custom role
        if ($customRole->accountUsers()->exists()) {
            return response()->json([
                'error' => 'Cannot delete custom role that is assigned to users'
            ], 422);
        }

        $customRole->delete();

        return response()->noContent();
    }

    /**
     * Get available permissions for custom roles
     */
    public function permissions(): JsonResponse
    {
        return response()->json([
            'data' => [
                'permissions' => CustomRole::getAvailablePermissions(),
                'descriptions' => CustomRole::getPermissionDescriptions(),
            ]
        ]);
    }
}
