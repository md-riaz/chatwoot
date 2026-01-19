<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AccountUser;
use App\Models\Account;
use App\Models\User;
use App\Enums\AccountUserRole;
use App\Enums\UserAvailability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AccountUsersController extends Controller
{
    /**
     * List all account users with filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AccountUser::with(['user:id,name,email,display_name', 'account:id,name,domain']);

        // Filter by account
        if ($request->has('account_id')) {
            $query->where('account_id', $request->input('account_id'));
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Filter by role
        if ($request->has('role')) {
            $role = $request->input('role');
            if (is_string($role)) {
                $role = AccountUserRole::fromName($role);
            }
            $query->where('role', $role->value);
        }

        // Filter by availability
        if ($request->has('availability')) {
            $query->where('availability', $request->input('availability'));
        }

        // Search by user name or email
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('display_name', 'like', "%{$search}%");
            });
        }

        // Search by account name
        if ($request->has('account_search')) {
            $accountSearch = $request->input('account_search');
            $query->whereHas('account', function ($q) use ($accountSearch) {
                $q->where('name', 'like', "%{$accountSearch}%")
                    ->orWhere('domain', 'like', "%{$accountSearch}%");
            });
        }

        $accountUsers = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 25));

        // Add role names to the response
        $accountUsers->getCollection()->transform(function ($accountUser) {
            $accountUser->role_name = $this->getRoleName($accountUser->role);
            $accountUser->availability_name = $this->getAvailabilityName($accountUser->availability);
            return $accountUser;
        });

        return response()->json($accountUsers);
    }

    /**
     * Show specific account user details.
     */
    public function show(AccountUser $accountUser): JsonResponse
    {
        $accountUser->load([
            'user:id,name,email,display_name,phone_number,created_at,email_verified_at',
            'account:id,name,domain,status,created_at'
        ]);

        $accountUser->role_name = $this->getRoleName($accountUser->role);
        $accountUser->availability_name = $this->getAvailabilityName($accountUser->availability);

        // Add additional stats
        $accountUser->stats = [
            'conversations_count' => $accountUser->user->conversations()
                ->where('account_id', $accountUser->account_id)
                ->count(),
            'messages_count' => $accountUser->user->messages()
                ->whereHas('conversation', function ($q) use ($accountUser) {
                    $q->where('account_id', $accountUser->account_id);
                })
                ->count(),
        ];

        return response()->json(['data' => $accountUser]);
    }

    /**
     * Create a new account user relationship.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'account_id' => 'required|exists:accounts,id',
            'role' => 'required|in:agent,administrator',
            'availability' => 'integer|in:0,1',
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors(),
            ], 422);
        }

        // Check if relationship already exists
        $existing = AccountUser::where('user_id', $request->input('user_id'))
            ->where('account_id', $request->input('account_id'))
            ->first();

        if ($existing) {
            return response()->json([
                'error' => 'User is already associated with this account',
            ], 409);
        }

        // Convert role name to integer if needed
        $role = AccountUserRole::fromName($request->input('role', 'agent'));
        $availability = UserAvailability::tryFrom($request->input('availability', 1)) ?? UserAvailability::ONLINE;

        $accountUser = AccountUser::create([
            'user_id' => $request->input('user_id'),
            'account_id' => $request->input('account_id'),
            'role' => $role,
            'availability' => $availability,
            'settings' => $request->input('settings'),
        ]);

        $accountUser->load(['user:id,name,email', 'account:id,name']);
        $accountUser->role_name = $this->getRoleName($accountUser->role);

        return response()->json(['data' => $accountUser], 201);
    }

    /**
     * Update an account user relationship.
     */
    public function update(Request $request, AccountUser $accountUser): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'role' => 'in:agent,administrator',
            'availability' => 'integer|in:0,1',
            'active_at' => 'boolean',
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors(),
            ], 422);
        }

        $updateData = [];

        // Update role if provided
        if ($request->has('role')) {
            $role = AccountUserRole::fromName($request->input('role'));
            $updateData['role'] = $role;
        }

        // Update other fields
        if ($request->has('availability')) {
            $updateData['availability'] = $request->input('availability');
        }

        if ($request->has('active_at')) {
            $updateData['active_at'] = $request->input('active_at');
        }

        if ($request->has('settings')) {
            $updateData['settings'] = $request->input('settings');
        }

        $accountUser->update($updateData);
        $accountUser->load(['user:id,name,email', 'account:id,name']);
        $accountUser->role_name = $this->getRoleName($accountUser->role);

        return response()->json(['data' => $accountUser]);
    }

    /**
     * Remove a user from an account.
     */
    public function destroy(AccountUser $accountUser): JsonResponse
    {
        // Check if this is the last admin for the account
        if ($accountUser->role->isAdministrator()) {
            $adminCount = AccountUser::where('account_id', $accountUser->account_id)
                ->where('role', AccountUserRole::ADMINISTRATOR)
                ->count();

            if ($adminCount <= 1) {
                return response()->json([
                    'error' => 'Cannot remove the last admin from the account',
                ], 422);
            }
        }

        $accountUser->delete();

        return response()->json(['message' => 'User removed from account successfully']);
    }

    /**
     * Bulk create account users.
     */
    public function bulkStore(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_users' => 'required|array|min:1',
            'account_users.*.user_id' => 'required|exists:users,id',
            'account_users.*.account_id' => 'required|exists:accounts,id',
            'account_users.*.role' => 'required|in:agent,administrator',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors(),
            ], 422);
        }

        $accountUsers = $request->input('account_users');
        $created = [];
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($accountUsers as $index => $data) {
                // Check if relationship already exists
                $existing = AccountUser::where('user_id', $data['user_id'])
                    ->where('account_id', $data['account_id'])
                    ->first();

                if ($existing) {
                    $errors[$index] = 'User already associated with account';
                    continue;
                }

                // Convert role name to integer if needed
                $role = AccountUserRole::fromName($data['role']);
                $availability = UserAvailability::tryFrom($data['availability'] ?? 1) ?? UserAvailability::ONLINE;

                $accountUser = AccountUser::create([
                    'user_id' => $data['user_id'],
                    'account_id' => $data['account_id'],
                    'role' => $role,
                    'availability' => $availability,
                ]);

                $created[] = $accountUser;
            }

            DB::commit();

            return response()->json([
                'message' => 'Bulk creation completed',
                'created' => count($created),
                'errors' => count($errors),
                'data' => $created,
                'error_details' => $errors,
            ], !empty($errors) ? 207 : 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => 'Bulk creation failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get account users statistics.
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total_relationships' => AccountUser::count(),
            'by_role' => [
                'agents' => AccountUser::where('role', AccountUserRole::AGENT)->count(),
                'administrators' => AccountUser::where('role', AccountUserRole::ADMINISTRATOR)->count(),
            ],
            'by_availability' => [
                'online' => AccountUser::where('availability', UserAvailability::ONLINE)->count(),
                'offline' => AccountUser::where('availability', UserAvailability::OFFLINE)->count(),
                'busy' => AccountUser::where('availability', UserAvailability::BUSY)->count(),
            ],
            'accounts_with_users' => Account::has('accountUsers')->count(),
            'users_with_accounts' => User::has('accountUsers')->count(),
        ];

        return response()->json(['data' => $stats]);
    }

    /**
     * Get role name from role enum value.
     */
    private function getRoleName($role): string
    {
        if ($role instanceof AccountUserRole) {
            return $role->name;
        }
        
        // Handle integer values
        return AccountUserRole::tryFrom($role)?->name ?? 'unknown';
    }

    /**
     * Get availability name from availability enum value.
     */
    private function getAvailabilityName($availability): string
    {
        if ($availability instanceof UserAvailability) {
            return $availability->name;
        }
        
        // Handle integer values
        return UserAvailability::tryFrom($availability)?->name ?? 'unknown';
    }

}
