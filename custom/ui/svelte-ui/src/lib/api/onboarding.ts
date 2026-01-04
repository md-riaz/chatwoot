/**
 * Onboarding API Client
 * Handles installation onboarding for super admin setup
 */

import { api } from './client';
import type { OnboardingData, ApiResponse } from './types';
import type { LoginResponse } from './auth';

/**
 * Onboarding completion parameters
 */
export interface OnboardingParams {
  name: string;
  company: string;
  email: string;
  password: string;
}

/**
 * Onboarding response
 */
export interface OnboardingResponse {
  message: string;
  user: {
    id: number;
    name: string;
    email: string;
  };
  account: {
    id: number;
    name: string;
  };
}

/**
 * Check onboarding status
 */
export async function checkOnboardingStatus(): Promise<OnboardingData> {
  const response = await api.get('api/v1/installation/onboarding/status', {
    skipAuth: true
  } as any).json<OnboardingData>();
  
  return response;
}

/**
 * Complete onboarding by creating super admin account
 */
export async function completeOnboarding(params: OnboardingParams): Promise<OnboardingResponse> {
  const response = await api.post('api/v1/installation/onboarding', {
    json: {
      user: params
    },
    skipAuth: true
  } as any).json<OnboardingResponse>();
  
  return response;
}
