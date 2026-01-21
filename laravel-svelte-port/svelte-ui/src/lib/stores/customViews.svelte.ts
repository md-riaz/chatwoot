import { page } from '$app/stores';
import { get } from 'svelte/store';
import * as customViewsAPI from '$lib/api/customViews';
import type { CustomView, CustomViewListParams } from '$lib/api/customViews';

/**
 * Custom Views Store using Svelte 5 Runes
 * Manages custom view (filter) data and operations
 */
class CustomViewsStore {
  // Reactive state using $state rune
  allCustomViews = $state<CustomView[]>([]);
  isLoading = $state<boolean>(false);
  error = $state<string | null>(null);

  // Computed values using $derived rune
  
  // Getter for current account ID from route
  get currentAccountId(): number {
    const pageStore = get(page);
    return Number(pageStore.params.accountId);
  }

  // Getter for conversation custom views
  get conversationViews(): CustomView[] {
    return this.allCustomViews.filter(view => view.filterType === 'conversation' || !view.filterType);
  }

  // Getter for contact custom views
  get contactViews(): CustomView[] {
    return this.allCustomViews.filter(view => view.filterType === 'contact');
  }

  /**
   * Fetch all custom views
   */
  async fetchCustomViews(params?: CustomViewListParams): Promise<void> {
    this.isLoading = true;
    this.error = null;

    try {
      const accountId = this.currentAccountId;
      if (!accountId) return;
      const views = await customViewsAPI.getCustomViews(accountId, params);
      this.allCustomViews = views;
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch custom views';
      console.error('Error fetching custom views:', err);
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Create a new custom view
   */
  async createCustomView(data: Partial<CustomView>): Promise<CustomView | null> {
    this.isLoading = true;
    this.error = null;

    try {
      const accountId = this.currentAccountId;
      if (!accountId) throw new Error('Account ID not found');
      
      const newView = await customViewsAPI.createCustomView(accountId, data);
      this.allCustomViews = [...this.allCustomViews, newView];
      return newView;
    } catch (err: any) {
      this.error = err.message || 'Failed to create custom view';
      console.error('Error creating custom view:', err);
      return null;
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Delete a custom view
   */
  async deleteCustomView(id: number): Promise<void> {
    this.isLoading = true;
    this.error = null;

    try {
      const accountId = this.currentAccountId;
      if (!accountId) throw new Error('Account ID not found');

      await customViewsAPI.deleteCustomView(accountId, id);
      this.allCustomViews = this.allCustomViews.filter(view => view.id !== id);
    } catch (err: any) {
      this.error = err.message || 'Failed to delete custom view';
      console.error('Error deleting custom view:', err);
    } finally {
      this.isLoading = false;
    }
  }
}

export const customViewsStore = new CustomViewsStore();
