import { page } from '$app/stores';
import { get } from 'svelte/store';
import * as attributesApi from '$lib/api/attributes';
import type {
  CustomAttribute,
  AttributeListParams,
  CreateAttributeParams,
  UpdateAttributeParams,
} from '$lib/api/attributes';

/**
 * Attributes Store using Svelte 5 runes
 * Manages custom attributes state and operations
 */
class AttributesStore {
  // Reactive state using $state rune
  allAttributes = $state<CustomAttribute[]>([]);
  selectedAttributeId = $state<number | null>(null);
  isLoading = $state<boolean>(false);
  isCreating = $state<boolean>(false);
  isUpdating = $state<boolean>(false);
  isDeleting = $state<boolean>(false);
  error = $state<string | null>(null);

  // Computed values using $derived rune
  selectedAttribute = $derived(
    this.allAttributes.find((a) => a.id === this.selectedAttributeId) || null
  );

  // Computed account ID from route params
  get currentAccountId(): number {
    const pageStore = get(page);
    return parseInt(pageStore.params.accountId || '0', 10);
  }

  get sortedAttributes(): CustomAttribute[] {
    return [...this.allAttributes].sort((a, b) => {
      const nameA = a.attributeDisplayName?.toLowerCase() || '';
      const nameB = b.attributeDisplayName?.toLowerCase() || '';
      return nameA.localeCompare(nameB);
    });
  }

  get contactAttributes(): CustomAttribute[] {
    return this.allAttributes.filter(
      (a) => a.attributeModel === 'contact_attribute'
    );
  }

  get conversationAttributes(): CustomAttribute[] {
    return this.allAttributes.filter(
      (a) => a.attributeModel === 'conversation_attribute'
    );
  }

  get attributesCount(): number {
    return this.allAttributes.length;
  }

  /**
   * Fetch all attributes
   */
  async fetchAttributes(params?: AttributeListParams): Promise<void> {
    if (!this.currentAccountId) return;

    try {
      this.isLoading = true;
      this.error = null;

      const attributes = await attributesApi.getCustomAttributes(
        this.currentAccountId,
        params
      );
      this.allAttributes = attributes || [];
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch attributes';
      console.error('Error fetching attributes:', err);
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Fetch a single attribute
   */
  async fetchAttribute(attributeId: number): Promise<CustomAttribute | null> {
    if (!this.currentAccountId) return null;

    try {
      this.isLoading = true;
      this.error = null;

      const attribute = await attributesApi.getCustomAttribute(
        this.currentAccountId,
        attributeId
      );

      // Update in the store if it exists
      const index = this.allAttributes.findIndex((a) => a.id === attribute.id);
      if (index !== -1) {
        this.allAttributes[index] = attribute;
      } else {
        this.allAttributes.push(attribute);
      }

      return attribute;
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch attribute';
      console.error('Error fetching attribute:', err);
      return null;
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Create a new attribute
   */
  async createAttribute(
    data: CreateAttributeParams
  ): Promise<CustomAttribute | null> {
    if (!this.currentAccountId) return null;

    try {
      this.isCreating = true;
      this.error = null;

      const newAttribute = await attributesApi.createCustomAttribute(
        this.currentAccountId,
        data
      );
      this.allAttributes.push(newAttribute);
      return newAttribute;
    } catch (err: any) {
      this.error = err.message || 'Failed to create attribute';
      console.error('Error creating attribute:', err);
      throw err;
    } finally {
      this.isCreating = false;
    }
  }

  /**
   * Update an existing attribute
   */
  async updateAttribute(
    attributeId: number,
    data: UpdateAttributeParams
  ): Promise<CustomAttribute | null> {
    if (!this.currentAccountId) return null;

    try {
      this.isUpdating = true;
      this.error = null;

      const updatedAttribute = await attributesApi.updateCustomAttribute(
        this.currentAccountId,
        attributeId,
        data
      );

      const index = this.allAttributes.findIndex((a) => a.id === attributeId);
      if (index !== -1) {
        this.allAttributes[index] = updatedAttribute;
      }

      return updatedAttribute;
    } catch (err: any) {
      this.error = err.message || 'Failed to update attribute';
      console.error('Error updating attribute:', err);
      throw err;
    } finally {
      this.isUpdating = false;
    }
  }

  /**
   * Delete an attribute
   */
  async deleteAttribute(attributeId: number): Promise<boolean> {
    if (!this.currentAccountId) return false;

    try {
      this.isDeleting = true;
      this.error = null;

      await attributesApi.deleteCustomAttribute(
        this.currentAccountId,
        attributeId
      );

      this.allAttributes = this.allAttributes.filter(
        (a) => a.id !== attributeId
      );
      if (this.selectedAttributeId === attributeId) {
        this.selectedAttributeId = null;
      }

      return true;
    } catch (err: any) {
      this.error = err.message || 'Failed to delete attribute';
      console.error('Error deleting attribute:', err);
      return false;
    } finally {
      this.isDeleting = false;
    }
  }

  /**
   * Select an attribute
   */
  selectAttribute(attributeId: number | null): void {
    this.selectedAttributeId = attributeId;
  }

  /**
   * Clear all attributes
   */
  clear(): void {
    this.allAttributes = [];
    this.selectedAttributeId = null;
    this.error = null;
  }
}

// Export singleton instance
export const attributesStore = new AttributesStore();
