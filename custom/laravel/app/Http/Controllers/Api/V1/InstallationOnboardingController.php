<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Account; // You may need to create this if not present
use App\Models\InstallationConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class InstallationOnboardingController extends Controller
{
    /**
     * Handle the first-time onboarding to create a super admin and account.
     */
    public function onboard(Request $request): JsonResponse
    {
        // Use Redis for onboarding flag (Rails-style)
        $redisKey = 'chatwoot_installation_onboarding';
        if (!app('redis')->get($redisKey)) {
            return response()->json(['error' => 'Onboarding already completed.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user.name' => 'required|string|max:255',
            'user.company' => 'required|string|max:255',
            'user.email' => 'required|email|max:255|unique:users,email',
            'user.password' => 'required|string|min:8',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->input('user');
        $subscribe = $request->input('subscribe_to_updates', false);

        DB::beginTransaction();
        try {
            // Create account (if Account model exists)
            $account = Account::create([
                'name' => $data['company'],
            ]);

            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
            $user->assignRole('super_admin');

            // Link user to account (if needed)
            if (method_exists($user, 'accounts')) {
                $user->accounts()->attach($account->id, ['role' => 'administrator']);
            }

            // Remove onboarding flag from Redis (block future onboarding)
            app('redis')->del($redisKey);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }

        // Optionally: handle subscribe_to_updates logic here

        return response()->json(['message' => 'Super admin and account created successfully.'], 201);
    }
}
