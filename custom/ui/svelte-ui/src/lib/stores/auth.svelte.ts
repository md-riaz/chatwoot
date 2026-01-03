/**
 * Auth Store using Svelte 5 runes
 * Replaces app/javascript/dashboard/store/modules/auth.js
 */

import { goto } from '$app/navigation';
import { page } from '$app/stores';
import { get } from 'svelte/store';
import * as authAPI from '$lib/api/auth';
import type { CurrentUser, ProfileUpdateParams, PasswordUpdateParams, AvailabilityUpdateParams } from '$lib/api/auth';
import { saveToStorage, loadFromStorage, clearStorage } from './persistence';

/**
 * Initial user state
 */
const initialUser: CurrentUser = {
  id: 0,
  accountId: 0,
  accounts: [],
  email: '',
  name: '',
};

/**
 * Auth store state using Svelte 5 runes
 */
class AuthStore {
  // Reactive state
  currentUser = $state<CurrentUser>(loadFromStorage<CurrentUser>('current_user') || initialUser);
  isFetching = $state<boolean>(true);
  error = $state<string | null>(null);
  
  // Computed values (getters) using $derived
  isLoggedIn = $derived(!!this.currentUser.id);
  currentUserId = $derived(this.currentUser.id);
  uiSettings = $derived(this.currentUser.uiSettings || {});
  messageSignature = $derived(this.currentUser.messageSignature || '');
  userAccounts = $derived(this.currentUser.accounts || []);
  
  /**
   * Get current account ID from route params
   */
  get currentAccountId(): number | null {
    const pageData = get(page);
    const accountId = pageData?.params?.accountId;
    return accountId ? Number(accountId) : null;
  }
  
  /**
   * Get current account from accounts list
   */
  get currentAccount() {
    const accountId = this.currentAccountId;
    if (!accountId) return null;
    
    return this.currentUser.accounts.find(acc => acc.id === accountId) || null;
  }
  
  /**
   * Get current user availability
   */
  get currentAvailability() {
    return this.currentAccount?.availability || 'offline';
  }
  
  /**
   * Get current user auto-offline setting
   */
  get currentAutoOffline() {
    return this.currentAccount?.autoOffline || false;
  }
  
  /**
   * Get current user role
   */
  get currentRole() {
    return this.currentAccount?.role || null;
  }
  
  /**
   * Get current user custom role ID
   */
  get currentCustomRoleId() {
    return this.currentAccount?.customRoleId || null;
  }
  
  /**
   * Check validity of current session
   */
  async validityCheck() {
    try {
      const user = await authAPI.validityCheck();
      this.setCurrentUser(user);
      this.error = null;
    } catch (err: any) {
      if (err?.response?.status === 401) {
        this.clearUser();
      }
      this.error = err.message || 'Failed to validate session';
      throw err;
    }
  }
  
  /**
   * Initialize user session
   */
  async setUser() {
    try {
      if (authAPI.hasAuthCookie()) {
        await this.validityCheck();
      } else {
        this.clearUser();
      }
    } finally {
      this.isFetching = false;
    }
  }
  
  /**
   * Logout current user
   */
  async logout() {
    try {
      await authAPI.logout();
    } catch (err) {
      // Ignore logout errors
      console.error('Logout error:', err);
    } finally {
      this.clearUser();
      
      // Clear all localStorage
      clearStorage('current_user');
      clearStorage('auth_token');
      
      // Redirect to login
      goto('/login');
    }
  }
  
  /**
   * Update user profile
   */
  async updateProfile(params: ProfileUpdateParams) {
    try {
      const user = await authAPI.updateProfile(params);
      this.setCurrentUser(user);
      this.error = null;
    } catch (err: any) {
      this.error = err.message || 'Failed to update profile';
      throw err;
    }
  }
  
  /**
   * Update user password
   */
  async updatePassword(params: PasswordUpdateParams) {
    try {
      const user = await authAPI.updatePassword(params);
      this.setCurrentUser(user);
      this.error = null;
    } catch (err: any) {
      this.error = err.message || 'Failed to update password';
      throw err;
    }
  }
  
  /**
   * Delete user avatar
   */
  async deleteAvatar() {
    try {
      const user = await authAPI.deleteAvatar();
      this.setCurrentUser(user);
      this.error = null;
    } catch (err) {
      // Ignore error
      console.error('Delete avatar error:', err);
    }
  }
  
  /**
   * Update UI settings
   */
  async updateUISettings(uiSettings: Record<string, any>) {
    // Optimistically update UI settings
    this.currentUser = {
      ...this.currentUser,
      uiSettings: {
        ...this.currentUser.uiSettings,
        ...uiSettings
      }
    };
    this.persistUser();
    
    try {
      // Don't update on server if impersonating
      const isImpersonating = loadFromStorage<boolean>('impersonating');
      if (!isImpersonating) {
        const user = await authAPI.updateUISettings(uiSettings);
        this.setCurrentUser(user);
      }
      this.error = null;
    } catch (err) {
      // Ignore error but log it
      console.error('Update UI settings error:', err);
    }
  }
  
  /**
   * Update availability status
   */
  async updateAvailability(params: AvailabilityUpdateParams) {
    const previousAvailability = this.currentAvailability;
    
    // Optimistically update availability
    this.setAvailability(params.availability);
    
    try {
      const user = await authAPI.updateAvailability(params);
      this.setCurrentUser(user);
      this.error = null;
      
      // TODO: Dispatch to agents store to update presence
      // dispatch('agents/updateSingleAgentPresence', {
      //   id: user.id,
      //   availabilityStatus: params.availability
      // });
    } catch (err: any) {
      // Revert on error
      this.setAvailability(previousAvailability);
      this.error = err.message || 'Failed to update availability';
      throw err;
    }
  }
  
  /**
   * Update auto-offline setting
   */
  async updateAutoOffline(accountId: number, autoOffline: boolean) {
    const previousAutoOffline = this.currentAutoOffline;
    
    // Optimistically update
    this.setAutoOffline(autoOffline);
    
    try {
      const user = await authAPI.updateAutoOffline(accountId, autoOffline);
      this.setCurrentUser(user);
      this.error = null;
    } catch (err: any) {
      // Revert on error
      this.setAutoOffline(previousAutoOffline);
      this.error = err.message || 'Failed to update auto-offline';
      throw err;
    }
  }
  
  /**
   * Set availability from external source (WebSocket)
   */
  setCurrentUserAvailability(data: Record<number, string>) {
    const userId = this.currentUser.id;
    if (data[userId]) {
      this.setAvailability(data[userId] as any);
    }
  }
  
  /**
   * Set active account
   */
  async setActiveAccount(accountId: number) {
    try {
      await authAPI.setActiveAccount(accountId);
      this.error = null;
    } catch (err) {
      // Ignore error
      console.error('Set active account error:', err);
    }
  }
  
  /**
   * Reset access token
   */
  async resetAccessToken(): Promise<boolean> {
    try {
      const user = await authAPI.resetAccessToken();
      this.setCurrentUser(user);
      this.error = null;
      return true;
    } catch (err) {
      this.error = 'Failed to reset access token';
      return false;
    }
  }
  
  /**
   * Resend confirmation email
   */
  async resendConfirmation() {
    try {
      await authAPI.resendConfirmation();
      this.error = null;
    } catch (err) {
      // Ignore error
      console.error('Resend confirmation error:', err);
    }
  }
  
  // Private helper methods
  
  /**
   * Set current user and persist
   */
  private setCurrentUser(user: CurrentUser) {
    this.currentUser = user;
    this.persistUser();
  }
  
  /**
   * Clear current user
   */
  private clearUser() {
    this.currentUser = initialUser;
    clearStorage('current_user');
  }
  
  /**
   * Set availability for current account
   */
  private setAvailability(availability: string) {
    const accountId = this.currentUser.accountId;
    const accounts = this.currentUser.accounts.map(account => {
      if (account.id === accountId) {
        return {
          ...account,
          availability: availability as any,
          availabilityStatus: availability as any
        };
      }
      return account;
    });
    
    this.currentUser = {
      ...this.currentUser,
      accounts
    };
    this.persistUser();
  }
  
  /**
   * Set auto-offline for current account
   */
  private setAutoOffline(autoOffline: boolean) {
    const accountId = this.currentUser.accountId;
    const accounts = this.currentUser.accounts.map(account => {
      if (account.id === accountId) {
        return { ...account, autoOffline };
      }
      return account;
    });
    
    this.currentUser = {
      ...this.currentUser,
      accounts
    };
    this.persistUser();
  }
  
  /**
   * Persist user to localStorage
   */
  private persistUser() {
    saveToStorage('current_user', this.currentUser);
  }
}

// Export singleton instance
export const authStore = new AuthStore();
