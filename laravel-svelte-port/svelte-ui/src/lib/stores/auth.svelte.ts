/**
 * Auth Store using Svelte 5 runes
 * Replaces app/javascript/dashboard/store/modules/auth.js
 */

import { goto } from '$app/navigation';
import { resolve } from '$app/paths';
import { page } from '$app/state';
import type {
  AvailabilityUpdateParams,
  CurrentUser,
  PasswordUpdateParams,
  ProfileUpdateParams,
} from '$lib/api/auth';
import * as authAPI from '$lib/api/auth';
import * as accountsAPI from '$lib/api/accounts';
import { getErrorMessage as getApiErrorMessage } from '$lib/api/errors';
import { clearStorage, loadFromStorage, saveToStorage } from './persistence';
import { globalConfig } from '$lib/stores/globalConfig.svelte';
import { agentsStore } from '$lib/stores/agents.svelte';

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
 * Auth store using Svelte 5 runes
 */
class AuthStore {
  // Reactive state using runes
  currentUser = $state<CurrentUser>(
    loadFromStorage<CurrentUser>('current_user') || initialUser
  );
  isFetching = $state<boolean>(true);
  error = $state<string | null>(null);

  // Computed values using $derived
  isLoggedIn = $derived(!!this.currentUser.id);
  currentUserId = $derived(this.currentUser.id);
  uiSettings = $derived(this.currentUser.uiSettings || {});
  messageSignature = $derived(this.currentUser.messageSignature || '');
  userAccounts = $derived(this.currentUser.accounts || []);

  get currentAccountId(): number | null {
    const accountId = page.params?.accountId;
    if (!accountId) return null;
    const id = Number(accountId);
    return isNaN(id) ? null : id;
  }

  /**
   * Get current account from accounts list
   * @param accountId Optional account ID to check. Defaults to route param.
   */
  getAccount(accountId?: number | null) {
    const id = accountId ?? this.currentAccountId;
    if (!id) return null;
    return this.currentUser.accounts.find(acc => acc.id === id) || null;
  }

  get currentAccount() {
    return this.getAccount();
  }

  /**
   * Check if user has a specific permission
   */
  hasPermission(permission: string, accountId?: number): boolean {
    const account = this.getAccount(accountId);
    if (!this.isLoggedIn || !account) return false;

    const role = account.role;
    if (role === 'administrator') return true;

    // If permission specifically asks for administrator, deny non-admins
    if (permission === 'administrator') return false;

    // For agents, check specific permissions if using custom roles
    // If not using custom roles, agents have basic permissions
    if (role === 'agent') {
      // Logic for standard agent vs custom role would go here
      // For now, assuming standard agent has access to basic features
      return true;
    }

    return false;
  }

  /**
   * Check if a feature is enabled for the current account
   */
  isFeatureEnabled(featureFlag: string, accountId?: number): boolean {
    // Check account specific features first
    const account = this.getAccount(accountId);
    if (
      account &&
      account.features &&
      account.features[featureFlag] !== undefined
    ) {
      return account.features[featureFlag];
    }

    // Fallback to global config
    return globalConfig.isFeatureEnabled(featureFlag);
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
    } catch (err: unknown) {
      const status =
        typeof err === 'object' &&
          err !== null &&
          'response' in err &&
          typeof (err as { response?: { status?: unknown } }).response?.status ===
          'number'
          ? (err as { response?: { status?: number } }).response?.status
          : undefined;

      if (status === 401) {
        this.clearUser();
      }
      this.error = getApiErrorMessage(err) || 'Failed to validate session';
      throw err;
    }
  }

  /**
   * Validate current session (alias for validityCheck for compatibility)
   */
  async validateSession() {
    return this.validityCheck();
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
      goto(resolve('/login'));
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
    } catch (err: unknown) {
      this.error = getApiErrorMessage(err) || 'Failed to update profile';
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
    } catch (err: unknown) {
      this.error = getApiErrorMessage(err) || 'Failed to update password';
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
  async updateUISettings(uiSettings: Record<string, unknown>) {
    // Optimistically update UI settings
    this.currentUser = {
      ...this.currentUser,
      uiSettings: {
        ...this.currentUser.uiSettings,
        ...uiSettings,
      },
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

      this.updateCurrentUserRealtimePresence(params.availability);
    } catch (err: unknown) {
      // Revert on error
      this.setAvailability(previousAvailability);
      this.error = getApiErrorMessage(err) || 'Failed to update availability';
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
    } catch (err: unknown) {
      // Revert on error
      this.setAutoOffline(previousAutoOffline);
      this.error = getApiErrorMessage(err) || 'Failed to update auto-offline';
      throw err;
    }
  }

  /**
   * Set availability from external source (WebSocket)
   */
  setCurrentUserAvailability(data: Record<number, string>) {
    const userId = this.currentUser.id;
    const availability = data[userId];

    if (
      availability === 'online' ||
      availability === 'offline' ||
      availability === 'busy'
    ) {
      this.setAvailability(availability);
      this.updateCurrentUserRealtimePresence(availability);
    }
  }

  /**
   * Sync current user presence into central agents store
   */
  updateCurrentUserRealtimePresence(
    availability: 'online' | 'offline' | 'busy'
  ) {
    if (!this.currentUser.id) return;
    agentsStore.updateSingleAgentPresence(this.currentUser.id, availability);
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
    } catch {
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

  /**
   * Update account details
   */
  async updateAccount(params: accountsAPI.UpdateAccountParams) {
    if (!this.currentAccountId) return;

    try {
      const updatedAccount = await accountsAPI.update(
        this.currentAccountId,
        params
      );

      // Update the account in the accounts list
      const accounts = this.currentUser.accounts.map(account => {
        if (account.id === this.currentAccountId) {
          return {
            ...account,
            ...updatedAccount,
          };
        }
        return account;
      });

      this.currentUser = {
        ...this.currentUser,
        accounts,
      };

      this.persistUser();
      this.error = null;
      return updatedAccount;
    } catch (err: unknown) {
      this.error = getApiErrorMessage(err) || 'Failed to update account';
      throw err;
    }
  }

  /**
   * Toggle account deletion
   */
  async toggleAccountDeletion(actionType: 'delete' | 'undelete') {
    if (!this.currentAccountId) return;
    try {
      await accountsAPI.toggleDeletion(this.currentAccountId, actionType);
      // Refresh user/account data to reflect changes
      await this.validityCheck();
    } catch (err: unknown) {
      this.error =
        getApiErrorMessage(err) || 'Failed to toggle account deletion';
      throw err;
    }
  }

  /**
   * Apply login payload to store and persistence layers.
   */
  setAuthenticatedUser(user: CurrentUser, token?: string) {
    if (token) {
      saveToStorage('auth_token', token);
    }

    this.setCurrentUser(user);

    this.error = null;
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
  private setAvailability(availability: 'online' | 'offline' | 'busy') {
    const accountId = this.currentUser.accountId;
    const accounts = this.currentUser.accounts.map(account => {
      if (account.id === accountId) {
        return {
          ...account,
          availability,
          availabilityStatus: availability,
        };
      }
      return account;
    });

    this.currentUser = {
      ...this.currentUser,
      accounts,
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
      accounts,
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
