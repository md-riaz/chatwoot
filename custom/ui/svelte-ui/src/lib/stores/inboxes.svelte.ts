import { page } from '$app/stores';
import { get } from 'svelte/store';
import * as inboxesAPI from '$lib/api/inboxes';
import type {
  Inbox,
  InboxListParams,
  CreateInboxParams,
  UpdateInboxParams,
  IMAPSettings,
  SMTPSettings,
  MessageTemplate,
} from '$lib/api/inboxes';
import { authStore } from './auth.svelte';

/**
 * Inboxes Store using Svelte 5 Runes
 * Manages inbox data and operations
 */
class InboxesStore {
  // Reactive state using $state rune
  allInboxes = $state<Inbox[]>([]);
  selectedInboxId = $state<number | null>(null);
  isLoading = $state<boolean>(false);
  error = $state<string | null>(null);
  uiFlags = $state({
    isFetching: false,
    isFetchingItem: false,
    isCreating: false,
    isUpdating: false,
    isDeleting: false,
    isUpdatingIMAP: false,
    isUpdatingSMTP: false,
    isSyncingTemplates: false,
  });

  // Computed values using $derived rune
  selectedInbox = $derived(
    this.allInboxes.find((inbox) => inbox.id === this.selectedInboxId) || null
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
    return authStore.currentUser?.accountId || 0;
  }

  // Helper to validate accountId before API calls
  private isValidAccountId(): boolean {
    return this.currentAccountId > 0;
  }

  // Getter for sorted inboxes (alphabetically by name)
  get sortedInboxes(): Inbox[] {
    return [...this.allInboxes].sort((a, b) => {
      const nameA = a.name?.toLowerCase() || '';
      const nameB = b.name?.toLowerCase() || '';
      return nameA.localeCompare(nameB);
    });
  }

  // Getter for inboxes by channel type
  getInboxesByType(channelType: string): Inbox[] {
    return this.allInboxes.filter((inbox) => inbox.channelType === channelType);
  }

  // Getter for WhatsApp templates for a specific inbox
  getWhatsAppTemplates(inboxId: number): MessageTemplate[] {
    const inbox = this.allInboxes.find((i) => i.id === inboxId);
    if (!inbox) return [];
    
    return inbox.messageTemplates || inbox.additionalAttributes?.message_templates || [];
  }

  // Getter for filtered WhatsApp templates (approved, non-authentication, etc.)
  getFilteredWhatsAppTemplates(inboxId: number): MessageTemplate[] {
    const templates = this.getWhatsAppTemplates(inboxId);
    
    if (!Array.isArray(templates)) return [];

    return templates.filter((template) => {
      // Ensure template has required properties
      if (!template || !template.status || !template.components) {
        return false;
      }

      // Only show approved templates
      if (template.status.toLowerCase() !== 'approved') {
        return false;
      }

      // Filter out authentication templates
      if (template.category === 'AUTHENTICATION') {
        return false;
      }

      // Filter out unsupported components
      const hasUnsupportedComponents = template.components.some(
        (component) =>
          ['LIST', 'PRODUCT', 'CATALOG', 'CALL_PERMISSION_REQUEST'].includes(component.type) ||
          (component.type === 'HEADER' && component.format === 'LOCATION')
      );

      if (hasUnsupportedComponents) {
        return false;
      }

      return true;
    });
  }

  /**
   * Fetch all inboxes
   */
  async fetchInboxes(params?: InboxListParams): Promise<void> {
    // Validate accountId before making API call
    if (!this.isValidAccountId()) {
      console.error('Cannot fetch inboxes: invalid account ID');
      return;
    }

    this.uiFlags.isFetching = true;
    this.error = null;

    try {
      const inboxes = await inboxesAPI.getInboxes(this.currentAccountId, params);
      this.allInboxes = inboxes;
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch inboxes';
      console.error('Error fetching inboxes:', err);
    } finally {
      this.uiFlags.isFetching = false;
    }
  }

  /**
   * Fetch single inbox
   */
  async fetchInbox(inboxId: number): Promise<void> {
    // Validate accountId before making API call
    if (!this.isValidAccountId()) {
      console.error('Cannot fetch inbox: invalid account ID');
      return;
    }

    this.uiFlags.isFetchingItem = true;
    this.error = null;

    try {
      const inbox = await inboxesAPI.getInbox(this.currentAccountId, inboxId);
      this.addOrUpdateInbox(inbox);
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch inbox';
      console.error('Error fetching inbox:', err);
    } finally {
      this.uiFlags.isFetchingItem = false;
    }
  }

  /**
   * Create new inbox
   */
  async createInbox(params: CreateInboxParams): Promise<Inbox | null> {
    // Validate accountId before making API call
    if (!this.isValidAccountId()) {
      console.error('Cannot create inbox: invalid account ID');
      this.error = 'Invalid account ID';
      return null;
    }

    this.uiFlags.isCreating = true;
    this.error = null;

    try {
      const inbox = await inboxesAPI.createInbox(this.currentAccountId, params);
      this.allInboxes = [...this.allInboxes, inbox];
      return inbox;
    } catch (err: any) {
      this.error = err.message || 'Failed to create inbox';
      console.error('Error creating inbox:', err);
      return null;
    } finally {
      this.uiFlags.isCreating = false;
    }
  }

  /**
   * Update inbox
   */
  async updateInbox(inboxId: number, params: UpdateInboxParams): Promise<Inbox | null> {
    this.uiFlags.isUpdating = true;
    this.error = null;

    try {
      const updatedInbox = await inboxesAPI.updateInbox(this.currentAccountId, inboxId, params);
      this.addOrUpdateInbox(updatedInbox);
      return updatedInbox;
    } catch (err: any) {
      this.error = err.message || 'Failed to update inbox';
      console.error('Error updating inbox:', err);
      return null;
    } finally {
      this.uiFlags.isUpdating = false;
    }
  }

  /**
   * Delete inbox
   */
  async deleteInbox(inboxId: number): Promise<boolean> {
    this.uiFlags.isDeleting = true;
    this.error = null;

    // Optimistic update
    const previousInboxes = this.allInboxes;
    this.allInboxes = this.allInboxes.filter((inbox) => inbox.id !== inboxId);

    try {
      await inboxesAPI.deleteInbox(this.currentAccountId, inboxId);
      return true;
    } catch (err: any) {
      // Rollback on error
      this.allInboxes = previousInboxes;
      this.error = err.message || 'Failed to delete inbox';
      console.error('Error deleting inbox:', err);
      return false;
    } finally {
      this.uiFlags.isDeleting = false;
    }
  }

  /**
   * Delete inbox avatar
   */
  async deleteInboxAvatar(inboxId: number): Promise<boolean> {
    this.error = null;

    try {
      await inboxesAPI.deleteInboxAvatar(this.currentAccountId, inboxId);
      
      // Update inbox in store
      const inbox = this.allInboxes.find((i) => i.id === inboxId);
      if (inbox) {
        inbox.avatarUrl = undefined;
        this.allInboxes = [...this.allInboxes];
      }

      return true;
    } catch (err: any) {
      this.error = err.message || 'Failed to delete inbox avatar';
      console.error('Error deleting inbox avatar:', err);
      return false;
    }
  }

  /**
   * Get agent bot for inbox
   */
  async getAgentBot(inboxId: number): Promise<any> {
    this.error = null;

    try {
      const bot = await inboxesAPI.getAgentBot(this.currentAccountId, inboxId);
      return bot;
    } catch (err: any) {
      this.error = err.message || 'Failed to get agent bot';
      console.error('Error getting agent bot:', err);
      return null;
    }
  }

  /**
   * Set agent bot for inbox
   */
  async setAgentBot(inboxId: number, botId: number | null): Promise<boolean> {
    this.error = null;

    try {
      await inboxesAPI.setAgentBot(this.currentAccountId, inboxId, botId);
      
      // Refresh inbox data
      await this.fetchInbox(inboxId);
      
      return true;
    } catch (err: any) {
      this.error = err.message || 'Failed to set agent bot';
      console.error('Error setting agent bot:', err);
      return false;
    }
  }

  /**
   * Sync WhatsApp templates for inbox
   */
  async syncTemplates(inboxId: number): Promise<MessageTemplate[]> {
    this.uiFlags.isSyncingTemplates = true;
    this.error = null;

    try {
      const templates = await inboxesAPI.syncTemplates(this.currentAccountId, inboxId);
      
      // Update inbox with new templates
      const inbox = this.allInboxes.find((i) => i.id === inboxId);
      if (inbox) {
        inbox.messageTemplates = templates;
        this.allInboxes = [...this.allInboxes];
      }

      return templates;
    } catch (err: any) {
      this.error = err.message || 'Failed to sync templates';
      console.error('Error syncing templates:', err);
      return [];
    } finally {
      this.uiFlags.isSyncingTemplates = false;
    }
  }

  /**
   * Update IMAP settings
   */
  async updateIMAPSettings(inboxId: number, settings: IMAPSettings): Promise<boolean> {
    this.uiFlags.isUpdatingIMAP = true;
    this.error = null;

    try {
      const updatedInbox = await inboxesAPI.updateIMAPSettings(this.currentAccountId, inboxId, settings);
      this.addOrUpdateInbox(updatedInbox);
      return true;
    } catch (err: any) {
      this.error = err.message || 'Failed to update IMAP settings';
      console.error('Error updating IMAP settings:', err);
      return false;
    } finally {
      this.uiFlags.isUpdatingIMAP = false;
    }
  }

  /**
   * Update SMTP settings
   */
  async updateSMTPSettings(inboxId: number, settings: SMTPSettings): Promise<boolean> {
    this.uiFlags.isUpdatingSMTP = true;
    this.error = null;

    try {
      const updatedInbox = await inboxesAPI.updateSMTPSettings(this.currentAccountId, inboxId, settings);
      this.addOrUpdateInbox(updatedInbox);
      return true;
    } catch (err: any) {
      this.error = err.message || 'Failed to update SMTP settings';
      console.error('Error updating SMTP settings:', err);
      return false;
    } finally {
      this.uiFlags.isUpdatingSMTP = false;
    }
  }

  /**
   * Add or update inbox in store (used by WebSocket events)
   */
  addOrUpdateInbox(inbox: Inbox): void {
    const index = this.allInboxes.findIndex((i) => i.id === inbox.id);
    if (index !== -1) {
      this.allInboxes[index] = inbox;
      this.allInboxes = [...this.allInboxes];
    } else {
      this.allInboxes = [...this.allInboxes, inbox];
    }
  }

  /**
   * Remove inbox from store (used by WebSocket events)
   */
  removeInbox(inboxId: number): void {
    this.allInboxes = this.allInboxes.filter((inbox) => inbox.id !== inboxId);
  }

  /**
   * Select inbox
   */
  selectInbox(inboxId: number | null): void {
    this.selectedInboxId = inboxId;
  }

  /**
   * Clear all inboxes
   */
  clearInboxes(): void {
    this.allInboxes = [];
    this.selectedInboxId = null;
  }

  /**
   * Reset store to initial state
   */
  reset(): void {
    this.allInboxes = [];
    this.selectedInboxId = null;
    this.isLoading = false;
    this.error = null;
    this.uiFlags = {
      isFetching: false,
      isFetchingItem: false,
      isCreating: false,
      isUpdating: false,
      isDeleting: false,
      isUpdatingIMAP: false,
      isUpdatingSMTP: false,
      isSyncingTemplates: false,
    };
  }
}

// Export singleton instance
export const inboxesStore = new InboxesStore();
