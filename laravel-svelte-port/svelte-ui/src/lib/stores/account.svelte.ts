/**
 * Account Store
 * Re-exports account-related functionality from auth store
 * This provides a cleaner API for components that only need account data
 */

import { authStore } from './auth.svelte';

/**
 * Account store - wrapper around auth store for account-specific operations
 */
class AccountStore {
  /**
   * Get current account
   */
  get currentAccount() {
    return authStore.currentAccount;
  }

  /**
   * Get current account ID
   */
  get currentAccountId() {
    return authStore.currentAccountId;
  }

  /**
   * Check if a feature is enabled for the current account
   */
  isFeatureEnabled(featureFlag: string): boolean {
    return authStore.isFeatureEnabled(featureFlag);
  }

  /**
   * Check if user has a specific permission in current account
   */
  hasPermission(permission: string): boolean {
    return authStore.hasPermission(permission);
  }

  /**
   * Get current user's role in the account
   */
  get currentRole() {
    return authStore.currentRole;
  }

  /**
   * Get current user's custom role ID
   */
  get currentCustomRoleId() {
    return authStore.currentCustomRoleId;
  }

  /**
   * Update account settings
   */
  async updateAccount(params: any) {
    return authStore.updateAccount(params);
  }

  /**
   * Toggle account deletion status
   */
  async toggleAccountDeletion(actionType: 'delete' | 'undelete') {
    return authStore.toggleAccountDeletion(actionType);
  }
}

// Export singleton instance
export const accountStore = new AccountStore();
