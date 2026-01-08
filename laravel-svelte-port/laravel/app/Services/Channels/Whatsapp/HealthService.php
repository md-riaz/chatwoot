<?php

namespace App\Services\Channels\Whatsapp;

use App\Models\Channels\Whatsapp;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HealthService
{
    private Whatsapp $channel;
    private FacebookApiClient $apiClient;

    public function __construct(Whatsapp $channel)
    {
        $this->channel = $channel;
        $this->apiClient = new FacebookApiClient($channel->provider_config['api_key']);
    }

    /**
     * Fetch health status for the WhatsApp channel
     */
    public function fetchHealthStatus(): array
    {
        try {
            $phoneNumberId = $this->channel->provider_config['phone_number_id'];
            $businessAccountId = $this->channel->provider_config['business_account_id'];

            // Get phone number info
            $phoneInfo = $this->apiClient->getPhoneNumberInfo($phoneNumberId);
            if (!$phoneInfo['success']) {
                throw new \Exception('Failed to fetch phone number info: ' . $phoneInfo['error']);
            }

            // Get business account info
            $businessInfo = $this->apiClient->getBusinessAccountInfo($businessAccountId);
            if (!$businessInfo['success']) {
                throw new \Exception('Failed to fetch business account info: ' . $businessInfo['error']);
            }

            return $this->parseHealthData($phoneInfo['data'], $businessInfo['data']);
        } catch (\Exception $e) {
            Log::error('WhatsApp health status fetch failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Check if channel requires reauthorization
     */
    public function requiresReauthorization(): bool
    {
        try {
            $healthData = $this->fetchHealthStatus();
            
            // Check for common indicators that reauthorization is needed
            return $healthData['verification_status'] !== 'VERIFIED' ||
                   $healthData['code_verification_status'] !== 'VERIFIED' ||
                   $healthData['platform_type'] === 'NOT_APPLICABLE';
        } catch (\Exception $e) {
            Log::error('WhatsApp reauthorization check failed', ['error' => $e->getMessage()]);
            // If we can't check, assume reauthorization is needed to be safe
            return true;
        }
    }

    /**
     * Get channel health summary
     */
    public function getHealthSummary(): array
    {
        try {
            $healthData = $this->fetchHealthStatus();
            
            return [
                'status' => $this->determineOverallStatus($healthData),
                'phone_verified' => $healthData['code_verification_status'] === 'VERIFIED',
                'business_verified' => $healthData['verification_status'] === 'VERIFIED',
                'platform_ready' => $healthData['platform_type'] !== 'NOT_APPLICABLE',
                'throughput_level' => $healthData['throughput']['level'] ?? 'UNKNOWN',
                'last_checked' => now()->toISOString(),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp health summary failed', ['error' => $e->getMessage()]);
            
            return [
                'status' => 'ERROR',
                'phone_verified' => false,
                'business_verified' => false,
                'platform_ready' => false,
                'throughput_level' => 'UNKNOWN',
                'last_checked' => now()->toISOString(),
                'error' => $e->getMessage(),
            ];
        }
    }

    private function parseHealthData(array $phoneData, array $businessData): array
    {
        return [
            // Phone number data
            'phone_number' => $phoneData['display_phone_number'] ?? null,
            'code_verification_status' => $phoneData['code_verification_status'] ?? 'UNKNOWN',
            'platform_type' => $phoneData['platform_type'] ?? 'NOT_APPLICABLE',
            'throughput' => [
                'level' => $phoneData['throughput']['level'] ?? 'NOT_APPLICABLE',
                'tier' => $phoneData['throughput']['tier'] ?? 'UNKNOWN',
            ],
            'quality_rating' => $phoneData['quality_rating'] ?? 'UNKNOWN',
            
            // Business account data
            'business_name' => $businessData['name'] ?? null,
            'verification_status' => $businessData['verification_status'] ?? 'UNKNOWN',
            'business_status' => $businessData['business_status'] ?? 'UNKNOWN',
            'timezone_id' => $businessData['timezone_id'] ?? null,
        ];
    }

    private function determineOverallStatus(array $healthData): string
    {
        // Check critical indicators
        if ($healthData['code_verification_status'] !== 'VERIFIED') {
            return 'PHONE_NOT_VERIFIED';
        }

        if ($healthData['verification_status'] !== 'VERIFIED') {
            return 'BUSINESS_NOT_VERIFIED';
        }

        if ($healthData['platform_type'] === 'NOT_APPLICABLE') {
            return 'NOT_PROVISIONED';
        }

        if (($healthData['throughput']['level'] ?? null) === 'NOT_APPLICABLE') {
            return 'NO_THROUGHPUT';
        }

        // Check quality rating
        $qualityRating = $healthData['quality_rating'] ?? 'UNKNOWN';
        if (in_array($qualityRating, ['RED', 'YELLOW'])) {
            return 'QUALITY_ISSUES';
        }

        return 'HEALTHY';
    }
}