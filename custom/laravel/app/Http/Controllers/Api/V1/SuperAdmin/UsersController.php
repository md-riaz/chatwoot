<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    /**
     * List all users (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Search filter
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('display_name', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->has('role')) {
            $query->role($request->input('role'));
        }

        $users = $query->with(['roles', 'accountUsers.account'])
            ->withCount('accounts')
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 25));

        // Transform users to match Rails format while keeping Laravel pagination
        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'display_name' => $user->display_name,
                'phone_number' => $user->phone_number,
                'avatar_url' => $user->avatar_url,
                'availability' => $user->availability,
                'confirmed' => !is_null($user->email_verified_at),
                'locked' => $user->custom_attributes['locked'] ?? false,
                'role' => $user->getRoleNames()->first() ?? 'agent',
                'roles' => $user->getRoleNames()->toArray(),
                'accounts_count' => $user->accounts_count,
                'custom_attributes' => $user->custom_attributes,
                'created_at' => $user->created_at?->toISOString(),
                'updated_at' => $user->updated_at?->toISOString(),
                'accounts' => $user->accountUsers->map(function ($accountUser) {
                    return [
                        'id' => $accountUser->account_id,
                        'name' => $accountUser->account->name ?? '',
                        'role' => $accountUser->role_name,
                        'availability' => $accountUser->availability_name,
                        'active_at' => $accountUser->active_at,
                    ];
                }),
            ];
        });

        return response()->json($users);
    }

    /**
     * Show user details.
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['roles', 'accountUsers.account']);

        $transformedUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'display_name' => $user->display_name,
            'phone_number' => $user->phone_number,
            'avatar_url' => $user->avatar_url,
            'availability' => $user->availability,
            'confirmed' => !is_null($user->email_verified_at),
            'locked' => $user->custom_attributes['locked'] ?? false,
            'role' => $user->getRoleNames()->first() ?? 'agent',
            'roles' => $user->getRoleNames()->toArray(),
            'custom_attributes' => $user->custom_attributes,
            'created_at' => $user->created_at?->toISOString(),
            'updated_at' => $user->updated_at?->toISOString(),
            'accounts' => $user->accountUsers->map(function ($accountUser) {
                return [
                    'id' => $accountUser->account_id,
                    'name' => $accountUser->account->name ?? '',
                    'role' => $accountUser->role_name,
                    'availability' => $accountUser->availability_name,
                    'active_at' => $accountUser->active_at,
                ];
            }),
        ];

        return response()->json(['data' => $transformedUser]);
    }

    /**
     * Create a new user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'display_name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'role' => 'nullable|string|in:agent,admin,super_admin',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'display_name' => $validated['display_name'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'email_verified_at' => now(),
        ]);

        if (isset($validated['role'])) {
            $user->assignRole($validated['role']);
        }

        $user->load(['roles', 'accountUsers.account']);

        $transformedUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'display_name' => $user->display_name,
            'phone_number' => $user->phone_number,
            'avatar_url' => $user->avatar_url,
            'availability' => $user->availability,
            'confirmed' => !is_null($user->email_verified_at),
            'locked' => $user->custom_attributes['locked'] ?? false,
            'role' => $user->getRoleNames()->first() ?? 'agent',
            'roles' => $user->getRoleNames()->toArray(),
            'custom_attributes' => $user->custom_attributes,
            'created_at' => $user->created_at?->toISOString(),
            'updated_at' => $user->updated_at?->toISOString(),
            'accounts' => $user->accountUsers->map(function ($accountUser) {
                return [
                    'id' => $accountUser->account_id,
                    'name' => $accountUser->account->name ?? '',
                    'role' => $accountUser->role_name,
                    'availability' => $accountUser->availability_name,
                    'active_at' => $accountUser->active_at,
                ];
            }),
        ];

        return response()->json(['data' => $transformedUser], 201);
    }

    /**
     * Update a user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => 'email|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8',
            'display_name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'availability' => 'nullable|integer',
            'confirmed_at' => 'nullable|date',
        ]);

        // Handle password update
        if (isset($validated['password']) && ! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Handle confirmation
        if (isset($validated['confirmed_at'])) {
            $validated['email_verified_at'] = $validated['confirmed_at'];
            unset($validated['confirmed_at']);
        }

        $user->update($validated);
        $user->load(['roles', 'accountUsers.account']);

        $transformedUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'display_name' => $user->display_name,
            'phone_number' => $user->phone_number,
            'avatar_url' => $user->avatar_url,
            'availability' => $user->availability,
            'confirmed' => !is_null($user->email_verified_at),
            'locked' => $user->custom_attributes['locked'] ?? false,
            'role' => $user->getRoleNames()->first() ?? 'agent',
            'roles' => $user->getRoleNames()->toArray(),
            'custom_attributes' => $user->custom_attributes,
            'created_at' => $user->created_at?->toISOString(),
            'updated_at' => $user->updated_at?->toISOString(),
            'accounts' => $user->accountUsers->map(function ($accountUser) {
                return [
                    'id' => $accountUser->account_id,
                    'name' => $accountUser->account->name ?? '',
                    'role' => $accountUser->role_name,
                    'availability' => $accountUser->availability_name,
                    'active_at' => $accountUser->active_at,
                ];
            }),
        ];

        return response()->json(['data' => $transformedUser]);
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json(['success' => true], 200);
    }

    /**
     * Upload user avatar.
     */
    public function uploadAvatar(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('avatars', $filename, 'public');
            
            $user->update(['avatar_url' => '/storage/' . $path]);
        }

        $user->load(['roles', 'accountUsers.account']);

        $transformedUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'display_name' => $user->display_name,
            'phone_number' => $user->phone_number,
            'avatar_url' => $user->avatar_url,
            'availability' => $user->availability,
            'confirmed' => !is_null($user->email_verified_at),
            'locked' => $user->custom_attributes['locked'] ?? false,
            'role' => $user->getRoleNames()->first() ?? 'agent',
            'roles' => $user->getRoleNames()->toArray(),
            'custom_attributes' => $user->custom_attributes,
            'created_at' => $user->created_at?->toISOString(),
            'updated_at' => $user->updated_at?->toISOString(),
            'accounts' => $user->accountUsers->map(function ($accountUser) {
                return [
                    'id' => $accountUser->account_id,
                    'name' => $accountUser->account->name ?? '',
                    'role' => $accountUser->role_name,
                    'availability' => $accountUser->availability_name,
                    'active_at' => $accountUser->active_at,
                ];
            }),
        ];

        return response()->json([
            'data' => $transformedUser,
            'message' => 'Avatar uploaded successfully.'
        ]);
    }

    /**
     * Delete user avatar.
     */
    public function destroyAvatar(User $user): JsonResponse
    {
        $user->update(['avatar_url' => null]);

        return response()->json(['message' => 'Avatar deleted.']);
    }

    /**
     * Confirm user email (equivalent to Rails skip_reconfirmation!).
     */
    public function confirmEmail(User $user): JsonResponse
    {
        $user->update(['email_verified_at' => now()]);
        $user->load(['roles', 'accountUsers.account']);

        $transformedUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'display_name' => $user->display_name,
            'phone_number' => $user->phone_number,
            'avatar_url' => $user->avatar_url,
            'availability' => $user->availability,
            'confirmed' => !is_null($user->email_verified_at),
            'locked' => $user->custom_attributes['locked'] ?? false,
            'role' => $user->getRoleNames()->first() ?? 'agent',
            'roles' => $user->getRoleNames()->toArray(),
            'custom_attributes' => $user->custom_attributes,
            'created_at' => $user->created_at?->toISOString(),
            'updated_at' => $user->updated_at?->toISOString(),
            'accounts' => $user->accountUsers->map(function ($accountUser) {
                return [
                    'id' => $accountUser->account_id,
                    'name' => $accountUser->account->name ?? '',
                    'role' => $accountUser->role_name,
                    'availability' => $accountUser->availability_name,
                    'active_at' => $accountUser->active_at,
                ];
            }),
        ];

        return response()->json([
            'data' => $transformedUser,
            'message' => 'User email confirmed successfully.'
        ]);
    }

    /**
     * Lock a user account.
     */
    public function lock(User $user): JsonResponse
    {
        // In Laravel, we can use a 'locked_at' timestamp or a boolean field
        // For now, we'll use a custom attribute to track lock status
        $customAttributes = $user->custom_attributes ?? [];
        $customAttributes['locked_at'] = now()->toISOString();
        $customAttributes['locked'] = true;
        
        $user->update(['custom_attributes' => $customAttributes]);
        $user->load(['roles', 'accountUsers.account']);

        $transformedUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'display_name' => $user->display_name,
            'phone_number' => $user->phone_number,
            'avatar_url' => $user->avatar_url,
            'availability' => $user->availability,
            'confirmed' => !is_null($user->email_verified_at),
            'locked' => $user->custom_attributes['locked'] ?? false,
            'role' => $user->getRoleNames()->first() ?? 'agent',
            'roles' => $user->getRoleNames()->toArray(),
            'custom_attributes' => $user->custom_attributes,
            'created_at' => $user->created_at?->toISOString(),
            'updated_at' => $user->updated_at?->toISOString(),
            'accounts' => $user->accountUsers->map(function ($accountUser) {
                return [
                    'id' => $accountUser->account_id,
                    'name' => $accountUser->account->name ?? '',
                    'role' => $accountUser->role_name,
                    'availability' => $accountUser->availability_name,
                    'active_at' => $accountUser->active_at,
                ];
            }),
        ];

        return response()->json([
            'data' => $transformedUser,
            'message' => 'User locked successfully.'
        ]);
    }

    /**
     * Unlock a user account.
     */
    public function unlock(User $user): JsonResponse
    {
        $customAttributes = $user->custom_attributes ?? [];
        unset($customAttributes['locked_at']);
        $customAttributes['locked'] = false;
        
        $user->update(['custom_attributes' => $customAttributes]);
        $user->load(['roles', 'accountUsers.account']);

        $transformedUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'display_name' => $user->display_name,
            'phone_number' => $user->phone_number,
            'avatar_url' => $user->avatar_url,
            'availability' => $user->availability,
            'confirmed' => !is_null($user->email_verified_at),
            'locked' => $user->custom_attributes['locked'] ?? false,
            'role' => $user->getRoleNames()->first() ?? 'agent',
            'roles' => $user->getRoleNames()->toArray(),
            'custom_attributes' => $user->custom_attributes,
            'created_at' => $user->created_at?->toISOString(),
            'updated_at' => $user->updated_at?->toISOString(),
            'accounts' => $user->accountUsers->map(function ($accountUser) {
                return [
                    'id' => $accountUser->account_id,
                    'name' => $accountUser->account->name ?? '',
                    'role' => $accountUser->role_name,
                    'availability' => $accountUser->availability_name,
                    'active_at' => $accountUser->active_at,
                ];
            }),
        ];

        return response()->json([
            'data' => $transformedUser,
            'message' => 'User unlocked successfully.'
        ]);
    }
}
