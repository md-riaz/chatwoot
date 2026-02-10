import { page } from '$app/stores';
import { get } from 'svelte/store';
import * as labelsAPI from '$lib/api/labels';
import type {
  Label,
  LabelListParams,
  CreateLabelParams,
  UpdateLabelParams,
} from '$lib/api/labels';
import { authStore } from './auth.svelte';

/**
 * Labels Store using Svelte 5 Runes
 * Manages label data and operations
 */
class LabelsStore {
  // Reactive state using $state rune
  allLabels = $state<Label[]>([]);
  selectedLabelId = $state<number | null>(null);
  isLoading = $state<boolean>(false);
  error = $state<string | null>(null);
  uiFlags = $state({
    isFetching: false,
    isFetchingItem: false,
    isCreating: false,
    isUpdating: false,
    isDeleting: false,
  });

  // Computed values using $derived rune
  selectedLabel = $derived(
    this.allLabels.find((label) => label.id === this.selectedLabelId) || null
  );

  // Getter for current account ID from route
  get currentAccountId(): number {
    const pageStore = get(page);
    const routeAccountId = pageStore.params.accountId;
    
    // Try to get accountId from route params first
    if (routeAccountId) {
      const parsed = parseInt(routeAccountId, 10);
      if (!isNaN(parsed) && parsed > 0) {
        return parsed;
      }
    }
    
    // Fall back to user's current account ID (with null safety)
    // Returns 0 if user is not logged in
    return authStore.currentUser?.accountId ?? 0;
  }

  // Getter for sorted labels (alphabetically by title)
  get sortedLabels(): Label[] {
    if (!Array.isArray(this.allLabels)) {
      console.error('LabelsStore: allLabels is not an array in sortedLabels', this.allLabels);
      return [];
    }
    return [...this.allLabels].sort((a, b) => {
      const titleA = a.title?.toLowerCase() || '';
      const titleB = b.title?.toLowerCase() || '';
      return titleA.localeCompare(titleB);
    });
  }

  // Getter for labels count
  get labelsCount(): number {
    return Array.isArray(this.allLabels) ? this.allLabels.length : 0;
  }

  // Getter for sidebar labels (labels with showOnSidebar = true)
  get sidebarLabels(): Label[] {
    if (!Array.isArray(this.allLabels)) {
      console.error('LabelsStore: allLabels is not an array in sidebarLabels', this.allLabels);
      return [];
    }
    return this.allLabels.filter((label) => label.showOnSidebar);
  }

  // Getter for labels sorted by color
  get labelsByColor(): Map<string, Label[]> {
    const labelsByColorMap = new Map<string, Label[]>();
    
    if (!Array.isArray(this.allLabels)) {
      console.error('LabelsStore: allLabels is not an array in labelsByColor', this.allLabels);
      return labelsByColorMap;
    }
    
    this.allLabels.forEach((label) => {
      const color = label.color || '#000000';
      if (!labelsByColorMap.has(color)) {
        labelsByColorMap.set(color, []);
      }
      labelsByColorMap.get(color)!.push(label);
    });

    return labelsByColorMap;
  }

  /**
   * Fetch all labels
   */
  async fetchLabels(params?: LabelListParams): Promise<void> {
    const accountId = this.currentAccountId;
    if (!accountId) return;

    this.uiFlags.isFetching = true;
    this.error = null;

    try {
      const labels = await labelsAPI.getLabels(accountId, params);
      if (Array.isArray(labels)) {
        this.allLabels = labels;
      } else {
        console.error('LabelsStore: fetchLabels returned non-array data', labels);
        // Fallback to empty array to prevent runtime errors
        this.allLabels = [];
        this.error = 'Received invalid data format for labels';
      }
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch labels';
      console.error('Error fetching labels:', err);
    } finally {
      this.uiFlags.isFetching = false;
    }
  }

  /**
   * Fetch single label
   */
  async fetchLabel(labelId: number): Promise<void> {
    this.uiFlags.isFetchingItem = true;
    this.error = null;

    try {
      const label = await labelsAPI.getLabel(this.currentAccountId, labelId);
      this.addOrUpdateLabel(label);
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch label';
      console.error('Error fetching label:', err);
    } finally {
      this.uiFlags.isFetchingItem = false;
    }
  }

  /**
   * Create new label
   */
  async createLabel(data: CreateLabelParams): Promise<Label | null> {
    const accountId = this.currentAccountId;
    if (!accountId) return null;

    this.uiFlags.isCreating = true;
    this.error = null;

    try {
      const newLabel = await labelsAPI.createLabel(accountId, data);
      this.allLabels = [...this.allLabels, newLabel];
      return newLabel;
    } catch (err: any) {
      this.error = err.message || 'Failed to create label';
      console.error('Error creating label:', err);
      return null;
    } finally {
      this.uiFlags.isCreating = false;
    }
  }

  /**
   * Update label
   */
  async updateLabel(id: number, data: UpdateLabelParams): Promise<Label | null> {
    const accountId = this.currentAccountId;
    if (!accountId) return null;

    this.uiFlags.isUpdating = true;
    this.error = null;

    try {
      const updatedLabel = await labelsAPI.updateLabel(accountId, id, data);
      this.allLabels = this.allLabels.map((label) =>
        label.id === id ? updatedLabel : label
      );
      if (this.selectedLabelId === id) {
        // Force reactivity update if needed
      }
      return updatedLabel;
    } catch (err: any) {
      this.error = err.message || 'Failed to update label';
      console.error('Error updating label:', err);
      return null;
    } finally {
      this.uiFlags.isUpdating = false;
    }
  }

  /**
   * Delete label
   */
  async deleteLabel(labelId: number): Promise<boolean> {
    this.uiFlags.isDeleting = true;
    this.error = null;

    // Optimistic update
    const previousLabels = this.allLabels;
    this.allLabels = this.allLabels.filter((label) => label.id !== labelId);

    try {
      await labelsAPI.deleteLabel(this.currentAccountId, labelId);
      return true;
    } catch (err: any) {
      // Rollback on error
      this.allLabels = previousLabels;
      this.error = err.message || 'Failed to delete label';
      console.error('Error deleting label:', err);
      return false;
    } finally {
      this.uiFlags.isDeleting = false;
    }
  }

  /**
   * Add or update label in store (used by WebSocket events)
   */
  addOrUpdateLabel(label: Label): void {
    const index = this.allLabels.findIndex((l) => l.id === label.id);
    if (index !== -1) {
      this.allLabels[index] = label;
      this.allLabels = [...this.allLabels];
    } else {
      this.allLabels = [...this.allLabels, label];
    }
  }

  /**
   * Remove label from store (used by WebSocket events)
   */
  removeLabel(labelId: number): void {
    this.allLabels = this.allLabels.filter((label) => label.id !== labelId);
  }

  /**
   * Select label
   */
  selectLabel(labelId: number | null): void {
    this.selectedLabelId = labelId;
  }

  /**
   * Clear all labels
   */
  clearLabels(): void {
    this.allLabels = [];
    this.selectedLabelId = null;
  }

  /**
   * Reset store to initial state
   */
  reset(): void {
    this.allLabels = [];
    this.selectedLabelId = null;
    this.isLoading = false;
    this.error = null;
    this.uiFlags = {
      isFetching: false,
      isFetchingItem: false,
      isCreating: false,
      isUpdating: false,
      isDeleting: false,
    };
  }

  /**
   * Revalidate labels with cache key (matching Vue implementation)
   * Used by WebSocket cache invalidation events
   */
  async revalidate(cacheKey?: string): Promise<void> {
    console.log('Revalidating labels store with cache key:', cacheKey);
    
    // Force refresh labels from server
    await this.fetchLabels();
    
    // Log successful revalidation
    console.log(`Labels store revalidated successfully. Count: ${this.labelsCount}`);
  }
}

// Export singleton instance
export const labelsStore = new LabelsStore();
