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
    
    // Fall back to user's current account ID
    return authStore.currentUser.accountId || 0;
  }

  // Getter for sorted labels (alphabetically by title)
  get sortedLabels(): Label[] {
    return [...this.allLabels].sort((a, b) => {
      const titleA = a.title?.toLowerCase() || '';
      const titleB = b.title?.toLowerCase() || '';
      return titleA.localeCompare(titleB);
    });
  }

  // Getter for labels count
  get labelsCount(): number {
    return this.allLabels.length;
  }

  // Getter for sidebar labels (labels with showOnSidebar = true)
  get sidebarLabels(): Label[] {
    return this.allLabels.filter((label) => label.showOnSidebar);
  }

  // Getter for labels sorted by color
  get labelsByColor(): Map<string, Label[]> {
    const labelsByColorMap = new Map<string, Label[]>();
    
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
    this.uiFlags.isFetching = true;
    this.error = null;

    try {
      const labels = await labelsAPI.getLabels(params);
      this.allLabels = labels;
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
      const label = await labelsAPI.getLabel(labelId);
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
  async createLabel(params: CreateLabelParams): Promise<Label | null> {
    this.uiFlags.isCreating = true;
    this.error = null;

    try {
      const label = await labelsAPI.createLabel(params);
      this.allLabels = [...this.allLabels, label];
      return label;
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
  async updateLabel(labelId: number, params: UpdateLabelParams): Promise<Label | null> {
    this.uiFlags.isUpdating = true;
    this.error = null;

    try {
      const updatedLabel = await labelsAPI.updateLabel(labelId, params);
      this.addOrUpdateLabel(updatedLabel);
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
      await labelsAPI.deleteLabel(labelId);
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
}

// Export singleton instance
export const labelsStore = new LabelsStore();
