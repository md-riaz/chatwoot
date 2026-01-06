/**
 * Automation Rules Store
 * Manages automation rules state using Svelte 5 runes
 */

import type {
    Automation,
    CreateAutomationParams,
    UpdateAutomationParams
} from '$lib/api/automation';
import * as automationApi from '$lib/api/automation';
import { authStore } from './auth.svelte';

interface AutomationState {
  all: Automation[];
  selectedId: number | null;
  isLoading: boolean;
  isSaving: boolean;
  isDeleting: boolean;
  error: string | null;
}

class AutomationStore {
  private state = $state<AutomationState>({
    all: [],
    selectedId: null,
    isLoading: false,
    isSaving: false,
    isDeleting: false,
    error: null
  });

  // Getters
  get all() {
    return this.state.all;
  }

  get isLoading() {
    return this.state.isLoading;
  }

  get isSaving() {
    return this.state.isSaving;
  }

  get isDeleting() {
    return this.state.isDeleting;
  }

  get error() {
    return this.state.error;
  }

  // Derived getters
  get selectedAutomation() {
    return (
      this.state.all.find(a => a.id === this.state.selectedId) || null
    );
  }

  get activeAutomations() {
    return (
      this.state.all.filter(a => a.active)
    );
  }

  get sortedAutomations() {
    return (
      [...this.state.all].sort((a, b) => 
        new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime()
      )
    );
  }

  get automationCount() {
    return (this.state.all.length);
  }

  get activeCount() {
    return (this.state.all.filter(a => a.active).length);
  }

  // Actions
  async fetchAutomations() {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    this.state.isLoading = true;
    this.state.error = null;

    try {
      const response = await automationApi.getAutomations(accountId);
      this.state.all = response.payload;
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to fetch automations';
      console.error('Error fetching automations:', error);
    } finally {
      this.state.isLoading = false;
    }
  }

  async createAutomation(params: CreateAutomationParams) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return null;

    this.state.isSaving = true;
    this.state.error = null;

    try {
      const automation = await automationApi.createAutomation(accountId, params);
      this.state.all = [...this.state.all, automation];
      return automation;
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to create automation';
      console.error('Error creating automation:', error);
      return null;
    } finally {
      this.state.isSaving = false;
    }
  }

  async updateAutomation(automationId: number, params: UpdateAutomationParams) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return null;

    this.state.isSaving = true;
    this.state.error = null;

    // Optimistic update
    const oldAutomation = this.state.all.find(a => a.id === automationId);
    if (oldAutomation) {
      this.state.all = this.state.all.map(a => 
        a.id === automationId ? { ...a, ...params } : a
      );
    }

    try {
      const updated = await automationApi.updateAutomation(accountId, automationId, params);
      this.state.all = this.state.all.map(a => 
        a.id === automationId ? updated : a
      );
      return updated;
    } catch (error) {
      // Rollback on error
      if (oldAutomation) {
        this.state.all = this.state.all.map(a => 
          a.id === automationId ? oldAutomation : a
        );
      }
      this.state.error = error instanceof Error ? error.message : 'Failed to update automation';
      console.error('Error updating automation:', error);
      return null;
    } finally {
      this.state.isSaving = false;
    }
  }

  async deleteAutomation(automationId: number) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return false;

    this.state.isDeleting = true;
    this.state.error = null;

    // Optimistic delete
    const deletedAutomation = this.state.all.find(a => a.id === automationId);
    this.state.all = this.state.all.filter(a => a.id !== automationId);

    try {
      await automationApi.deleteAutomation(accountId, automationId);
      return true;
    } catch (error) {
      // Rollback on error
      if (deletedAutomation) {
        this.state.all = [...this.state.all, deletedAutomation];
      }
      this.state.error = error instanceof Error ? error.message : 'Failed to delete automation';
      console.error('Error deleting automation:', error);
      return false;
    } finally {
      this.state.isDeleting = false;
    }
  }

  async cloneAutomation(automationId: number) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return null;

    this.state.isSaving = true;
    this.state.error = null;

    try {
      const cloned = await automationApi.cloneAutomation(accountId, automationId);
      this.state.all = [...this.state.all, cloned];
      return cloned;
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to clone automation';
      console.error('Error cloning automation:', error);
      return null;
    } finally {
      this.state.isSaving = false;
    }
  }

  async uploadFile(file: File) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return null;

    try {
      return await automationApi.attachFile(accountId, file);
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to upload file';
      console.error('Error uploading file:', error);
      return null;
    }
  }

  selectAutomation(id: number | null) {
    this.state.selectedId = id;
  }

  getAutomationById(id: number) {
    return this.state.all.find(a => a.id === id) || null;
  }

  clearError() {
    this.state.error = null;
  }

  reset() {
    this.state = {
      all: [],
      selectedId: null,
      isLoading: false,
      isSaving: false,
      isDeleting: false,
      error: null
    };
  }
}

export const automationStore = new AutomationStore();
