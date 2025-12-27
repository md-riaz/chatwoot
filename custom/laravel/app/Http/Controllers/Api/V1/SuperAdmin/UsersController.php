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

        $users = $query->with('roles')
            ->withCount('accounts')
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 25));

        return response()->json($users);
    }

    /**
     * Show user details.
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['roles', 'accounts']);

        return response()->json(['data' => $user]);
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

        return response()->json(['data' => $user], 201);
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

        return response()->json(['data' => $user]);
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json(null, 204);
    }

    /**
     * Delete user avatar.
     */
    public function destroyAvatar(User $user): JsonResponse
    {
        $user->update(['avatar_url' => null]);

        return response()->json(['message' => 'Avatar deleted.']);
    }
}
