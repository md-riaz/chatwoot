<?php


namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
// No imports needed for Redis or JsonResponse, use fully qualified names

class InstallationOnboardingStatusController extends Controller
{
    /**
     * Get the onboarding status for the installation.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function status()
    {
        // Use Redis for onboarding flag (Rails-style)
        $redisKey = 'chatwoot_installation_onboarding';
        $onboardingPending = \Illuminate\Support\Facades\Redis::get($redisKey) ? true : false;
        return response()->json([
            'onboarding_pending' => $onboardingPending
        ]);
    }
}
