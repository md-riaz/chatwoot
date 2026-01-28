import { goto } from '$app/navigation';
import { page } from '$app/stores';
import * as contactsApi from '$lib/api/contacts';
import type {
  Contact,
  CreateContactParams,
  UpdateContactParams,
  ContactListParams,
  ContactFilterParams,
} from '$lib/api/contacts';
import { saveToStorage, loadFromStorage, clearStorage } from './persistence';
import { ApiError } from '$lib/api/errors';
import { get } from 'svelte/store';

/**
 * Contacts Store using Svelte 5 runes
 * Manages contact state, CRUD operations, search, filtering, and merging
 */
class ContactsStore {
  // Reactive state using $state rune
  allContacts = $state<Contact[]>([]);
  selectedContactId = $state<number | null>(null);
  isLoading = $state<boolean>(false);
  error = $state<string | null>(null);
  validationErrors = $state<Record<string, string>>({});
  searchQuery = $state<string>('');
  appliedFilters = $state<ContactFilterParams>({});
  currentPage = $state<number>(1);
  hasMorePages = $state<boolean>(true);

  // Computed values using $derived rune
  selectedContact = $derived(
    this.allContacts.find((c) => c.id === this.selectedContactId) || null
  );

  // Computed account ID from route params
  get currentAccountId(): number {
    const pageStore = get(page);
    return parseInt(pageStore.params.accountId || '0', 10);
  }

  // Complex computed values using getters
  get sortedContacts(): Contact[] {
    return [...this.allContacts].sort((a, b) => {
      // Sort by name alphabetically
      const nameA = a.name?.toLowerCase() || '';
      const nameB = b.name?.toLowerCase() || '';
      return nameA.localeCompare(nameB);
    });
  }

  get filteredContacts(): Contact[] {
    let contacts = this.sortedContacts;

    // Filter by search query
    if (this.searchQuery.trim()) {
      const query = this.searchQuery.toLowerCase();
      contacts = contacts.filter(
        (contact) =>
          contact.name?.toLowerCase().includes(query) ||
          contact.email?.toLowerCase().includes(query) ||
          contact.phoneNumber?.includes(query) ||
          contact.identifier?.toLowerCase().includes(query)
      );
    }

    return contacts;
  }

  get contactsCount(): number {
    return this.allContacts.length;
  }

  /**
   * Fetch contacts with pagination
   */
  async fetchContacts(params: ContactListParams = {}): Promise<void> {
    if (!this.currentAccountId) return;

    try {
      this.isLoading = true;
      this.error = null;

      const response = await contactsApi.getContacts(
        this.currentAccountId,
        params
      );

      this.allContacts = response.data || [];
      this.currentPage = params.page || 1;
      this.hasMorePages = !!response.meta?.nextPage;
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch contacts';
      console.error('Error fetching contacts:', err);
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Fetch active contacts
   */
  async fetchActiveContacts(params: ContactListParams = {}): Promise<void> {
    if (!this.currentAccountId) return;

    try {
      this.isLoading = true;
      this.error = null;

      const response = await contactsApi.getActiveContacts(
        this.currentAccountId,
        params
      );

      this.allContacts = response.data || [];
      this.currentPage = params.page || 1;
      this.hasMorePages = !!response.meta?.nextPage;
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch active contacts';
      console.error('Error fetching active contacts:', err);
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Fetch contacts for a specific segment
   */
  async fetchSegmentContacts(segmentId: number, params: ContactListParams = {}): Promise<void> {
    if (!this.currentAccountId) return;

    try {
      this.isLoading = true;
      this.error = null;

      // Dynamic import to avoid circular dependencies if any, 
      // though typically stores import apis.
      const { getSegmentContacts } = await import('$lib/api/segments');

      const response = await getSegmentContacts(
        this.currentAccountId,
        segmentId,
        params
      );

      this.allContacts = response.data || [];
      this.currentPage = params.page || 1;
      this.hasMorePages = !!response.meta?.nextPage;
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch segment contacts';
      console.error('Error fetching segment contacts:', err);
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Search contacts by query
   */
  async searchContacts(query: string, page = 1): Promise<void> {
    if (!this.currentAccountId) return;

    try {
      this.isLoading = true;
      this.error = null;
      this.searchQuery = query;

      const response = await contactsApi.searchContacts(
        this.currentAccountId,
        query,
        page
      );

      this.allContacts = response.data || [];
      this.currentPage = page;
      this.hasMorePages = !!response.meta?.nextPage;
    } catch (err: any) {
      this.error = err.message || 'Failed to search contacts';
      console.error('Error searching contacts:', err);
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Filter contacts with advanced filters
   */
  async filterContacts(filters: ContactFilterParams): Promise<void> {
    if (!this.currentAccountId) return;

    try {
      this.isLoading = true;
      this.error = null;
      this.appliedFilters = filters;

      const response = await contactsApi.filterContacts(
        this.currentAccountId,
        filters
      );

      this.allContacts = response.data || [];
      this.currentPage = filters.page || 1;
      this.hasMorePages = !!response.meta?.nextPage;
    } catch (err: any) {
      this.error = err.message || 'Failed to filter contacts';
      console.error('Error filtering contacts:', err);
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Fetch single contact by ID
   */
  async fetchContact(contactId: number): Promise<void> {
    if (!this.currentAccountId) return;

    try {
      this.isLoading = true;
      this.error = null;

      const contact = await contactsApi.getContact(
        this.currentAccountId,
        contactId
      );

      // Update or add contact to store
      const index = this.allContacts.findIndex((c) => c.id === contact.id);
      if (index >= 0) {
        this.allContacts[index] = contact;
      } else {
        this.allContacts.push(contact);
      }
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch contact';
      console.error('Error fetching contact:', err);
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Create new contact
   */
  async createContact(params: CreateContactParams): Promise<Contact | null> {
    if (!this.currentAccountId) return null;

    try {
      this.isLoading = true;
      this.error = null;
      this.validationErrors = {};

      const contact = await contactsApi.createContact(
        this.currentAccountId,
        params
      );

      // Add to store
      this.allContacts.push(contact);

      return contact;
    } catch (err: any) {
      if (err instanceof ApiError && err.isValidationError()) {
        const errors = err.data.errors || err.data.details || {};
        Object.entries(errors).forEach(([key, msgs]) => {
          this.validationErrors[key] = Array.isArray(msgs) ? msgs[0] : msgs as string;
        });
      }
      this.error = err.message || 'Failed to create contact';
      console.error('Error creating contact:', err);
      return null;
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Update existing contact
   */
  async updateContact(
    contactId: number,
    params: UpdateContactParams
  ): Promise<Contact | null> {
    if (!this.currentAccountId) return null;

    try {
      this.isLoading = true;
      this.error = null;
      this.validationErrors = {};

      const contact = await contactsApi.updateContact(
        this.currentAccountId,
        contactId,
        params
      );

      // Update in store
      const index = this.allContacts.findIndex((c) => c.id === contactId);
      if (index >= 0) {
        this.allContacts[index] = contact;
      }

      return contact;
    } catch (err: any) {
      if (err instanceof ApiError && err.isValidationError()) {
        const errors = err.data.errors || err.data.details || {};
        Object.entries(errors).forEach(([key, msgs]) => {
          this.validationErrors[key] = Array.isArray(msgs) ? msgs[0] : msgs as string;
        });
      }
      this.error = err.message || 'Failed to update contact';
      console.error('Error updating contact:', err);
      return null;
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Delete contact with optimistic update
   */
  async deleteContact(contactId: number): Promise<boolean> {
    if (!this.currentAccountId) return false;

    // Store original state for rollback
    const originalContacts = [...this.allContacts];

    try {
      // Optimistic update: remove immediately
      this.allContacts = this.allContacts.filter((c) => c.id !== contactId);

      await contactsApi.deleteContact(this.currentAccountId, contactId);

      // Clear selection if deleted contact was selected
      if (this.selectedContactId === contactId) {
        this.selectedContactId = null;
      }

      return true;
    } catch (err: any) {
      // Rollback on error
      this.allContacts = originalContacts;
      this.error = err.message || 'Failed to delete contact';
      console.error('Error deleting contact:', err);
      return false;
    }
  }

  /**
   * Delete contact avatar
   */
  async deleteContactAvatar(contactId: number): Promise<boolean> {
    if (!this.currentAccountId) return false;

    try {
      const contact = await contactsApi.deleteContactAvatar(
        this.currentAccountId,
        contactId
      );

      // Update in store
      const index = this.allContacts.findIndex((c) => c.id === contactId);
      if (index >= 0) {
        this.allContacts[index] = contact;
      }

      return true;
    } catch (err: any) {
      this.error = err.message || 'Failed to delete avatar';
      console.error('Error deleting contact avatar:', err);
      return false;
    }
  }

  /**
   * Merge two contacts (primary absorbs secondary)
   */
  async mergeContacts(
    primaryContactId: number,
    secondaryContactId: number
  ): Promise<boolean> {
    if (!this.currentAccountId) return false;

    try {
      this.isLoading = true;
      this.error = null;

      const mergedContact = await contactsApi.mergeContacts(
        this.currentAccountId,
        primaryContactId,
        secondaryContactId
      );

      // Update primary contact and remove secondary
      const primaryIndex = this.allContacts.findIndex(
        (c) => c.id === primaryContactId
      );
      if (primaryIndex >= 0) {
        this.allContacts[primaryIndex] = mergedContact;
      }

      this.allContacts = this.allContacts.filter(
        (c) => c.id !== secondaryContactId
      );

      return true;
    } catch (err: any) {
      this.error = err.message || 'Failed to merge contacts';
      console.error('Error merging contacts:', err);
      return false;
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Import contacts from file
   */
  async importContacts(
    file: File
  ): Promise<{ success: boolean; failed: number; total: number } | null> {
    if (!this.currentAccountId) return null;

    try {
      this.isLoading = true;
      this.error = null;

      const result = await contactsApi.importContacts(
        this.currentAccountId,
        file
      );

      // Refresh contact list after import
      await this.fetchContacts();

      return result;
    } catch (err: any) {
      this.error = err.message || 'Failed to import contacts';
      console.error('Error importing contacts:', err);
      return null;
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Export contacts to file
   */
  async exportContacts(): Promise<boolean> {
    if (!this.currentAccountId) return false;

    try {
      const blob = await contactsApi.exportContacts(this.currentAccountId);

      // Create download link
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `contacts-${Date.now()}.csv`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      window.URL.revokeObjectURL(url);

      return true;
    } catch (err: any) {
      this.error = err.message || 'Failed to export contacts';
      console.error('Error exporting contacts:', err);
      return false;
    }
  }

  /**
   * Add or update contact (from WebSocket event)
   */
  addOrUpdateContact(contact: Contact): void {
    const index = this.allContacts.findIndex((c) => c.id === contact.id);
    if (index >= 0) {
      this.allContacts[index] = contact;
    } else {
      this.allContacts.push(contact);
    }
  }

  /**
   * Remove contact (from WebSocket event)
   */
  removeContact(contactId: number): void {
    this.allContacts = this.allContacts.filter((c) => c.id !== contactId);
    if (this.selectedContactId === contactId) {
      this.selectedContactId = null;
    }
  }

  /**
   * Select contact
   */
  selectContact(contactId: number | null): void {
    this.selectedContactId = contactId;
  }

  /**
   * Set search query
   */
  setSearchQuery(query: string): void {
    this.searchQuery = query;
  }

  /**
   * Clear filters and search
   */
  clearFilters(): void {
    this.searchQuery = '';
    this.appliedFilters = {};
  }

  /**
   * Clear all contacts
   */
  clearContacts(): void {
    this.allContacts = [];
    this.selectedContactId = null;
    this.searchQuery = '';
    this.appliedFilters = {};
    this.currentPage = 1;
    this.hasMorePages = true;
  }

  /**
   * Reset store to initial state
   */
  reset(): void {
    this.clearContacts();
    this.isLoading = false;
    this.error = null;
  }

  /**
   * Bulk assign labels to selected contacts
   */
  async bulkAssignLabels(
    contactIds: number[],
    labels: string[]
  ): Promise<boolean> {
    if (!this.currentAccountId || contactIds.length === 0) return false;

    try {
      this.isLoading = true;
      this.error = null;

      await contactsApi.bulkAssignLabels(
        this.currentAccountId,
        contactIds,
        labels
      );

      // Refresh contacts to get updated labels
      await this.fetchContacts();
      return true;
    } catch (err: any) {
      this.error = err.message || 'Failed to assign labels';
      console.error('Error assigning labels:', err);
      return false;
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Bulk delete selected contacts
   */
  async bulkDelete(contactIds: number[]): Promise<boolean> {
    if (!this.currentAccountId || contactIds.length === 0) return false;

    try {
      this.isLoading = true;
      this.error = null;

      await contactsApi.bulkDeleteContacts(this.currentAccountId, contactIds);

      // Remove deleted contacts from local state
      this.allContacts = this.allContacts.filter(
        (c) => !contactIds.includes(c.id)
      );

      return true;
    } catch (err: any) {
      this.error = err.message || 'Failed to delete contacts';
      console.error('Error deleting contacts:', err);
      return false;
    } finally {
      this.isLoading = false;
    }
  }
}

// Export singleton instance
export const contactsStore = new ContactsStore();
