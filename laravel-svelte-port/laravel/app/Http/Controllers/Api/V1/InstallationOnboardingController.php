<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Actions\Account\CreateAccountAction;
use App\Actions\Account\SignUpEmailValidationAction;
use App\Data\Account\AccountData;
use App\Models\User;
use App\Models\AccountUser;
use App\Enums\AccountUserRole;
use App\Exceptions\InvalidEmailException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Optional;

class InstallationOnboardingController extends Controller
{
    public function __construct(
        private CreateAccountAction $createAccount,
        private SignUpEmailValidationAction $emailValidation
    ) {}

    /**
     * Show onboarding information (Rails equivalent of index action).
     */
    public function index(): JsonResponse
    {
        // Use Redis for onboarding flag (Rails-style)
        $redisKey = 'chatwoot_installation_onboarding';
        if (!app('redis')->get($redisKey)) {
            return response()->json(['error' => 'Onboarding already completed.'], 403);
        }

        return response()->json([
            'message' => 'Installation onboarding required.',
            'onboarding_pending' => true
        ]);
    }

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

        $userData = $request->input('user');

        try {
            // Validate email using existing action
            $this->emailValidation->handle($userData['email']);
        } catch (InvalidEmailException $e) {
            $details = $e->getDetails();
            $message = 'Invalid email address';
            
            if (isset($details['domain_blocked']) && $details['domain_blocked']) {
                $message = 'Email domain is blocked';
            } elseif (isset($details['disposable']) && $details['disposable']) {
                $message = 'Disposable email addresses are not allowed';
            } elseif (isset($details['valid']) && !$details['valid']) {
                $message = 'Invalid email format';
            }
            
            return response()->json(['errors' => ['email' => [$message]]], 422);
        }

        try {
            return DB::transaction(function () use ($userData, $redisKey) {
                // Ensure configuration is loaded before creating account
                $enabledFeatures = \App\Enums\Feature::getEnabledByDefault();
                \App\Models\InstallationConfig::updateOrCreate(
                    ['name' => 'ACCOUNT_LEVEL_FEATURE_DEFAULTS'],
                    [
                        'display_title' => 'Account Level Feature Defaults',
                        'description' => 'Default features enabled for new accounts',
                        'type' => 'array',
                        'locked' => true,
                        'serialized_value' => $enabledFeatures,
                    ]
                );

                // Create account using existing action
                $accountData = new AccountData(
                    id: Optional::create(),
                    name: $userData['company'],
                    locale: app()->getLocale(),
                    domain: null,
                    support_email: null,
                    settings: Optional::create(),
                    features: Optional::create(),
                    limits: Optional::create(),
                    status: 0  // 0 = Active, 1 = Suspended
                );
                
                $account = $this->createAccount->handle($accountData);

                // Create user (following existing pattern from other controllers)
                $user = User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => Hash::make($userData['password']),
                    'email_verified_at' => now(), // confirmed = true
                    'type' => 'SuperAdmin', // Set type instead of role
                ]);

                // Link user to account as administrator
                AccountUser::create([
                    'account_id' => $account->id,
                    'user_id' => $user->id,
                    'role' => AccountUserRole::ADMINISTRATOR,
                    'availability' => \App\Enums\UserAvailability::ONLINE, // Default to online
                ]);

                // Remove onboarding flag from Redis (block future onboarding)
                app('redis')->del($redisKey);

                // Load relationships for complete user data
                $user->load(['accountUsers.account', 'roles']);
                
                // Create API token for automatic login
                $token = $user->createToken('onboarding-token')->plainTextToken;

                return response()->json([
                    'message' => 'Super admin and account created successfully.',
                    'user' => new \App\Http\Resources\User\UserResource($user),
                    'token' => $token,
                    'account' => [
                        'id' => $account->id,
                        'name' => $account->name,
                        'enabled_features' => $account->getEnabledFeatures(),
                        'feature_flags' => $account->feature_flags,
                    ]
                ], 201);
            });

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}