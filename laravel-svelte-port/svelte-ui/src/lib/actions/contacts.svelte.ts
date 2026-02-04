/**
 * Contact Actions using Svelte 5 Reactive Classes
 * Provides Vue-like composable functionality for contact operations
 * 
 * This demonstrates the Mutation/Action pattern with:
 * - Reactive state management
 * - Optimistic updates
 * - Error handling
 * - Loading states
 * - Automatic retries
 */

import * as contactsApi from '$lib/api/contacts';
import type {
  Contact,
  CreateContactParams,
  UpdateContactParams,
  ContactListParams,
  AdvancedFilterCondition,
} from '$lib/api/contacts';
import { BaseAction, QueryAction, MutationAction, createQuery, createMutation } from './base.svelte.ts';
import type { PaginatedResponse } from '$lib/api/types';

/**
 * Contact List Query Action
 * Handles fetching paginated contact lists with search and filtering
 */
export class ContactListQuery extends QueryAction<PaginatedResponse<Contact>, ContactListParams & { accountId: number }> {
  constructor() {
    super(
      async ({ accountId, ...params }) => {
        return contactsApi.getContacts(accountId, params);
      },
      {
        retry: {
          attempts: 3,
          delay: 1000,
          backoff: 'exponential'
        }
      }
    );
  }
}

/**
 * Contact Search Query Action
 * Handles contact search with debouncing and caching
 */
export class ContactSearchQuery extends QueryAction<PaginatedResponse<Contact>, { accountId: number; query: string; page?: number }> {
  private searchTimeout: number | null = null;
  
  constructor() {
    super(
      async ({ accountId, query, page = 1 }) => {
        return contactsApi.searchContacts(accountId, query, page);
      },
      {
        retry: {
          attempts: 2,
          delay: 500
        }
      }
    );
  }
  
  /**
   * Debounced search execution
   */
  async searchDebounced(variables: { accountId: number; query: string; page?: number }, delay = 300): Promise<void> {
    if (this.searchTimeout) {
      clearTimeout(this.searchTimeout);
    }
    
    this.searchTimeout = window.setTimeout(() => {
      this.execute(variables);
    }, delay);
  }
}

/**
 * Contact Filter Query Action
 * Handles advanced filtering with condition chaining
 */
export class ContactFilterQuery extends QueryAction<PaginatedResponse<Contact>, { 
  accountId: number; 
  filters: AdvancedFilterCondition[]; 
  page?: number; 
  sortAttr?: string; 
}> {
  constructor() {
    super(
      async ({ accountId, filters, page = 1, sortAttr = 'name' }) => {
        return contactsApi.filterContacts(accountId, filters, page, sortAttr);
      }
    );
  }
}

/**
 * Single Contact Query Action
 * Handles fetching individual contact details
 */
export class ContactQuery extends QueryAction<Contact, { accountId: number; contactId: number }> {
  constructor() {
    super(
      async ({ accountId, contactId }) => {
        return contactsApi.getContact(accountId, contactId);
      },
      {
        retry: {
          attempts: 3,
          delay: 1000
        }
      }
    );
  }
}

/**
 * Create Contact Mutation Action
 * Handles contact creation with validation error handling
 */
export class CreateContactMutation extends MutationAction<Contact, { accountId: number } & CreateContactParams> {
  validationErrors = $state<Record<string, string>>({});
  
  constructor() {
    super(
      async ({ accountId, ...params }) => {
        return contactsApi.createContact(accountId, params);
      },
      {
        onSuccess: (data, variables) => {
          console.log('Contact created successfully:', data.name);
          // Clear validation errors on success
          this.validationErrors = {};
        },
        onError: (error, variables) => {
          // Handle validation errors
          if (error.isValidationError?.()) {
            const errors = error.data?.errors || error.data?.details || {};
            this.validationErrors = {};
            Object.entries(errors).forEach(([key, msgs]) => {
              this.validationErrors[key] = Array.isArray(msgs) ? msgs[0] : msgs as string;
            });
          }
        },
        retry: {
          attempts: 2,
          delay: 1000
        }
      }
    );
  }
  
  /**
   * Reset validation errors
   */
  clearValidationErrors(): void {
    this.validationErrors = {};
  }
}

/**
 * Update Contact Mutation Action
 * Handles contact updates with optimistic updates
 */
export class UpdateContactMutation extends MutationAction<Contact, { 
  accountId: number; 
  contactId: number; 
} & UpdateContactParams> {
  validationErrors = $state<Record<string, string>>({});
  
  constructor(private originalContact?: Contact) {
    super(
      async ({ accountId, contactId, ...params }) => {
        return contactsApi.updateContact(accountId, contactId, params);
      },
      {
        // Optimistic update: immediately show changes
        optimisticUpdate: (variables) => {
          if (!this.originalContact) return this.originalContact as Contact;
          
          return {
            ...this.originalContact,
            ...variables,
            updatedAt: new Date().toISOString()
          };
        },
        onSuccess: (data, variables) => {
          console.log('Contact updated successfully:', data.name);
          this.validationErrors = {};
        },
        onError: (error, variables) => {
          console.error('Failed to update contact:', error.message);
          
          // Handle validation errors
          if (error.isValidationError?.()) {
            const errors = error.data?.errors || error.data?.details || {};
            this.validationErrors = {};
            Object.entries(errors).forEach(([key, msgs]) => {
              this.validationErrors[key] = Array.isArray(msgs) ? msgs[0] : msgs as string;
            });
          }
        },
        onRollback: (variables) => {
          console.log('Rolling back optimistic update for contact:', variables.contactId);
        }
      }
    );
  }
  
  /**
   * Set original contact for optimistic updates
   */
  setOriginalContact(contact: Contact): void {
    this.originalContact = contact;
  }
}

/**
 * Delete Contact Mutation Action
 * Handles contact deletion with optimistic removal
 */
export class DeleteContactMutation extends MutationAction<void, { accountId: number; contactId: number }> {
  constructor() {
    super(
      async ({ accountId, contactId }) => {
        await contactsApi.deleteContact(accountId, contactId);
      },
      {
        onSuccess: (data, variables) => {
          console.log('Contact deleted successfully:', variables.contactId);
        },
        onError: (error, variables) => {
          console.error('Failed to delete contact:', error.message);
        }
      }
    );
  }
}

/**
 * Merge Contacts Mutation Action
 * Handles merging two contacts with complex state updates
 */
export class MergeContactsMutation extends MutationAction<Contact, { 
  accountId: number; 
  primaryContactId: number; 
  secondaryContactId: number; 
}> {
  constructor() {
    super(
      async ({ accountId, primaryContactId, secondaryContactId }) => {
        return contactsApi.mergeContacts(accountId, primaryContactId, secondaryContactId);
      },
      {
        onSuccess: (data, variables) => {
          console.log('Contacts merged successfully:', {
            primary: variables.primaryContactId,
            secondary: variables.secondaryContactId
          });
        }
      }
    );
  }
}

/**
 * Import Contacts Mutation Action
 * Handles file upload with progress tracking
 */
export class ImportContactsMutation extends MutationAction<{ success: boolean; failed: number; total: number }, { 
  accountId: number; 
  file: File; 
}> {
  uploadProgress = $state<number>(0);
  
  constructor() {
    super(
      async ({ accountId, file }) => {
        return contactsApi.importContacts(accountId, file);
      },
      {
        onSuccess: (data, variables) => {
          console.log('Contacts imported:', data);
          this.uploadProgress = 100;
        },
        onError: (error, variables) => {
          console.error('Import failed:', error.message);
          this.uploadProgress = 0;
        }
      }
    );
  }
  
  /**
   * Update upload progress
   */
  setProgress(progress: number): void {
    this.uploadProgress = progress;
  }
}

/**
 * Bulk Actions Mutation
 * Handles bulk operations on multiple contacts
 */
export class BulkContactsMutation extends MutationAction<{ success: boolean; updated?: number; deleted?: number }, {
  accountId: number;
  contactIds: number[];
  action: 'assign_labels' | 'delete';
  labels?: string[];
}> {
  constructor() {
    super(
      async ({ accountId, contactIds, action, labels }) => {
        switch (action) {
          case 'assign_labels':
            if (!labels) throw new Error('Labels required for assign_labels action');
            return contactsApi.bulkAssignLabels(accountId, contactIds, labels);
          case 'delete':
            return contactsApi.bulkDeleteContacts(accountId, contactIds);
          default:
            throw new Error(`Unknown bulk action: ${action}`);
        }
      },
      {
        onSuccess: (data, variables) => {
          console.log(`Bulk ${variables.action} completed:`, data);
        }
      }
    );
  }
}

/**
 * Composable Contact Actions Factory
 * Creates a complete set of contact actions for a specific account
 * 
 * This provides Vue-like composable functionality:
 * - Reactive state management
 * - Automatic cleanup
 * - Consistent error handling
 * - Optimistic updates
 */
export class ContactActions {
  // Query actions
  list = new ContactListQuery();
  search = new ContactSearchQuery();
  filter = new ContactFilterQuery();
  single = new ContactQuery();
  
  // Mutation actions
  create = new CreateContactMutation();
  update = new UpdateContactMutation();
  delete = new DeleteContactMutation();
  merge = new MergeContactsMutation();
  import = new ImportContactsMutation();
  bulk = new BulkContactsMutation();
  
  // Derived state - combines all loading states
  isAnyLoading = $derived(
    this.list.loading || 
    this.search.loading || 
    this.filter.loading || 
    this.single.loading || 
    this.create.loading || 
    this.update.loading || 
    this.delete.loading || 
    this.merge.loading || 
    this.import.loading || 
    this.bulk.loading
  );
  
  // Derived state - combines all errors
  hasAnyError = $derived(
    !!this.list.error || 
    !!this.search.error || 
    !!this.filter.error || 
    !!this.single.error || 
    !!this.create.error || 
    !!this.update.error || 
    !!this.delete.error || 
    !!this.merge.error || 
    !!this.import.error || 
    !!this.bulk.error
  );
  
  constructor(private accountId: number) {
    // Auto-cleanup on component unmount
    $effect(() => {
      return () => {
        this.cleanup();
      };
    });
  }
  
  /**
   * Fetch contacts with parameters
   */
  async fetchContacts(params: ContactListParams = {}): Promise<void> {
    await this.list.execute({ accountId: this.accountId, ...params });
  }
  
  /**
   * Search contacts with debouncing
   */
  async searchContacts(query: string, page = 1): Promise<void> {
    await this.search.searchDebounced({ 
      accountId: this.accountId, 
      query, 
      page 
    });
  }
  
  /**
   * Filter contacts with advanced conditions
   */
  async filterContacts(filters: AdvancedFilterCondition[], page = 1, sortAttr = 'name'): Promise<void> {
    await this.filter.execute({
      accountId: this.accountId,
      filters,
      page,
      sortAttr
    });
  }
  
  /**
   * Get single contact
   */
  async getContact(contactId: number): Promise<void> {
    await this.single.execute({ accountId: this.accountId, contactId });
  }
  
  /**
   * Create new contact
   */
  async createContact(params: CreateContactParams): Promise<Contact | null> {
    return this.create.execute({ accountId: this.accountId, ...params });
  }
  
  /**
   * Update existing contact with optimistic update
   */
  async updateContact(contactId: number, params: UpdateContactParams, originalContact?: Contact): Promise<Contact | null> {
    if (originalContact) {
      this.update.setOriginalContact(originalContact);
    }
    return this.update.execute({ accountId: this.accountId, contactId, ...params });
  }
  
  /**
   * Delete contact
   */
  async deleteContact(contactId: number): Promise<void> {
    await this.delete.execute({ accountId: this.accountId, contactId });
  }
  
  /**
   * Merge two contacts
   */
  async mergeContacts(primaryContactId: number, secondaryContactId: number): Promise<Contact | null> {
    return this.merge.execute({
      accountId: this.accountId,
      primaryContactId,
      secondaryContactId
    });
  }
  
  /**
   * Import contacts from file
   */
  async importContacts(file: File): Promise<void> {
    await this.import.execute({ accountId: this.accountId, file });
  }
  
  /**
   * Bulk assign labels
   */
  async bulkAssignLabels(contactIds: number[], labels: string[]): Promise<void> {
    await this.bulk.execute({
      accountId: this.accountId,
      contactIds,
      action: 'assign_labels',
      labels
    });
  }
  
  /**
   * Bulk delete contacts
   */
  async bulkDeleteContacts(contactIds: number[]): Promise<void> {
    await this.bulk.execute({
      accountId: this.accountId,
      contactIds,
      action: 'delete'
    });
  }
  
  /**
   * Reset all actions
   */
  reset(): void {
    this.list.reset();
    this.search.reset();
    this.filter.reset();
    this.single.reset();
    this.create.reset();
    this.update.reset();
    this.delete.reset();
    this.merge.reset();
    this.import.reset();
    this.bulk.reset();
  }
  
  /**
   * Cancel all ongoing requests
   */
  cancelAll(): void {
    this.list.cancel();
    this.search.cancel();
    this.filter.cancel();
    this.single.cancel();
    this.create.cancel();
    this.update.cancel();
    this.delete.cancel();
    this.merge.cancel();
    this.import.cancel();
    this.bulk.cancel();
  }
  
  /**
   * Cleanup resources
   */
  private cleanup(): void {
    this.cancelAll();
  }
}

/**
 * Factory function to create contact actions (Vue-like composable)
 */
export function useContactActions(accountId: number): ContactActions {
  return new ContactActions(accountId);
}