/**
 * Auth API Client
 * Replaces app/javascript/dashboard/api/auth.js
 */

import { api, uploadFile } from './client';
import type { ApiResponse } from './types';

/**
 * User account interface
 */
export interface UserAccount {
  id: number;
  name: string;
  role: string;
  customRoleId?: number;
  availability: 'online' | 'offline' | 'busy';
  availabilityStatus?: 'online' | 'offline' | 'busy';
  autoOffline: boolean;
  features?: Record<string, boolean>;
  locale?: string;
  domain?: string;
  supportEmail?: string;
  settings?: Record<string, any>;
  avatarUrl?: string;
  latestChatwootVersion?: string;
  customAttributes?: Record<string, any>;
}

/**
 * Current user interface
 */
export interface CurrentUser {
  id: number;
  name: string;
  email: string;
  displayName?: string;
  phoneNumber?: string;
  avatarUrl?: string;
  availability?: number;
  customAttributes?: Record<string, any>;
  emailVerifiedAt?: string;
  createdAt?: string;
  updatedAt?: string;
  
  // Rails parity fields
  type?: 'User' | 'SuperAdmin';
  confirmed?: boolean;
  locked?: boolean;
  roles?: string[];
  
  // Account relationships
  accounts: UserAccount[];
  
  // Legacy fields for backward compatibility
  accountId?: number;
  avatar?: string;
  messageSignature?: string;
  uiSettings?: Record<string, any>;
  accessToken?: string;
}

/**
 * Login parameters
 */
export interface LoginParams {
  email: string;
  password: string;
}

/**
 * Login response
 */
export interface LoginResponse {
  user: CurrentUser;
  token: string;
}

/**
 * Register parameters
 */
export interface RegisterParams {
  name: string;
  email: string;
  password: string;
  passwordConfirmation: string;
}

/**
 * Register response
 */
export interface RegisterResponse {
  message: string;
  data: {
    user: CurrentUser;
    token: string;
    email_confirmation_sent: boolean;
  };
  user?: CurrentUser;
  token?: string;
}

/**
 * Profile update parameters
 */
export interface ProfileUpdateParams {
  displayName?: string;
  avatar?: File;
  messageSignature?: string;
  [key: string]: any;
}

/**
 * Password update parameters
 */
export interface PasswordUpdateParams {
  currentPassword: string;
  password: string;
  passwordConfirmation: string;
}

/**
 * Availability update parameters
 */
export interface AvailabilityUpdateParams {
  availability: 'online' | 'offline' | 'busy';
  accountId?: number;
}

/**
 * Login user
 */
export async function login(params: LoginParams): Promise<LoginResponse> {
  const response = await api.post('api/v1/auth/login', {
    json: params
  }).json<LoginResponse>();
  
  return response;
}

/**
 * Register new user
 */
export async function register(params: RegisterParams): Promise<RegisterResponse> {
  const response = await api.post('api/v1/auth/register', {
    json: params
  }).json<RegisterResponse>();
  
  // Normalize response structure
  if (response.data) {
    return {
      ...response,
      user: response.data.user,
      token: response.data.token
    };
  }
  
  return response;
}

/**
 * Check if user is authenticated by validating current session
 */
export async function validityCheck(): Promise<CurrentUser> {
  const response = await api.get('api/v1/auth/me').json<{ data: CurrentUser }>();
  return response.data;
}

/**
 * Logout current user
 */
export async function logout(): Promise<void> {
  await api.post('api/v1/auth/logout');
}

/**
 * Check if auth cookie exists (for SSR compatibility)
 */
export function hasAuthCookie(): boolean {
  if (typeof document === 'undefined') return false;
  
  return document.cookie
    .split('; ')
    .some(cookie => cookie.startsWith('cw_d_session_info='));
}

/**
 * Get auth data from cookie
 */
export function getAuthData(): any {
  if (!hasAuthCookie()) return null;
  
  const cookie = document.cookie
    .split('; ')
    .find(cookie => cookie.startsWith('cw_d_session_info='));
  
  if (!cookie) return null;
  
  try {
    const value = cookie.split('=')[1];
    return JSON.parse(decodeURIComponent(value));
  } catch {
    return null;
  }
}

/**
 * Update user profile
 */
export async function updateProfile(params: ProfileUpdateParams): Promise<CurrentUser> {
  const { avatar, displayName, ...otherParams } = params;
  
  // If avatar is included, use multipart/form-data
  if (avatar) {
    const formData = new FormData();
    
    // Add profile fields
    Object.keys(otherParams).forEach(key => {
      if (otherParams[key] !== undefined) {
        formData.append(`profile[${key}]`, otherParams[key]);
      }
    });
    
    if (displayName !== undefined) {
      formData.append('profile[display_name]', displayName);
    }
    
    formData.append('profile[avatar]', avatar);
    
    const response = await uploadFile(
      'api/v1/profile',
      formData,
      {
        method: 'PATCH'
      }
    ) as CurrentUser;
    return response;
  }
  
  // Regular JSON update
  const payload: Record<string, any> = {};
  
  Object.keys(otherParams).forEach(key => {
    if (otherParams[key] !== undefined) {
      payload[key] = otherParams[key];
    }
  });
  
  if (displayName !== undefined) {
    payload.displayName = displayName;
  }
  
  const response = await api.patch('api/v1/profile', {
    json: { profile: payload }
  }).json<CurrentUser>();
  
  return response;
}

/**
 * Update user password
 */
export async function updatePassword(params: PasswordUpdateParams): Promise<CurrentUser> {
  const response = await api.patch('api/v1/profile/password', {
    json: {
      profile: {
        currentPassword: params.currentPassword,
        password: params.password,
        passwordConfirmation: params.passwordConfirmation
      }
    }
  }).json<CurrentUser>();
  
  return response;
}

/**
 * Update UI settings
 */
export async function updateUISettings(uiSettings: Record<string, any>): Promise<CurrentUser> {
  const response = await api.patch('api/v1/profile', {
    json: {
      profile: { uiSettings }
    }
  }).json<CurrentUser>();
  
  return response;
}

/**
 * Update availability status
 */
export async function updateAvailability(params: AvailabilityUpdateParams): Promise<CurrentUser> {
  const response = await api.patch('api/v1/profile/availability', {
    json: {
      profile: params
    }
  }).json<CurrentUser>();
  
  return response;
}

/**
 * Update auto-offline setting
 */
export async function updateAutoOffline(accountId: number, autoOffline: boolean): Promise<CurrentUser> {
  const response = await api.patch('api/v1/profile/auto_offline', {
    json: {
      profile: {
        accountId,
        autoOffline
      }
    }
  }).json<CurrentUser>();
  
  return response;
}

/**
 * Delete user avatar
 */
export async function deleteAvatar(): Promise<CurrentUser> {
  const response = await api.delete('api/v1/profile/avatar').json<CurrentUser>();
  return response;
}

/**
 * Reset password (forgot password flow)
 */
export async function resetPassword(email: string): Promise<void> {
  await api.post('api/v1/auth/password/email', {
    json: { email }
  });
}

/**
 * Set active account
 */
export async function setActiveAccount(accountId: number): Promise<void> {
  await api.put('api/v1/profile/set_active_account', {
    json: {
      profile: { accountId }
    }
  });
}

/**
 * Resend confirmation email
 */
export async function resendConfirmation(): Promise<void> {
  await api.post('api/v1/profile/resend_confirmation');
}

/**
 * Reset access token
 */
export async function resetAccessToken(): Promise<CurrentUser> {
  const response = await api.post('api/v1/profile/reset_access_token').json<CurrentUser>();
  return response;
}
