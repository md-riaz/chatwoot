/**
 * Macros Store
 * Manages macros state using Svelte 5 runes
 */

import * as macrosApi from '$lib/api/macros';
import { authStore } from './auth.svelte';
import type { 
  Macro, 
  CreateMacroParams, 
  UpdateMacroParams 
} from '$lib/api/macros';

interface MacrosState {
  all: Macro[];
  selectedId: number | null;
  isLoading: boolean;
  isSaving: boolean;
  isDeleting: boolean;
  isExecuting: boolean;
  error: string | null;
}

class MacrosStore {
  private state = $state<MacrosState>({
    all: [],
    selectedId: null,
    isLoading: false,
    isSaving: false,
    isDeleting: false,
    isExecuting: false,
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

  get isExecuting() {
    return this.state.isExecuting;
  }

  get error() {
    return this.state.error;
  }

  // Derived getters
  get selectedMacro() {
    return (
      this.state.all.find(m => m.id === this.state.selectedId) || null
    );
  }

  get globalMacros() {
    return (
      this.state.all.filter(m => m.visibility === 'global')
    );
  }

  get personalMacros() {
    return (
      this.state.all.filter(m => m.visibility === 'personal')
    );
  }

  get sortedMacros() {
    return (
      [...this.state.all].sort((a, b) => a.name.localeCompare(b.name))
    );
  }

  get macroCount() {
    return (this.state.all.length);
  }

  // Actions
  async fetchMacros() {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    this.state.isLoading = true;
    this.state.error = null;

    try {
      const response = await macrosApi.getMacros(accountId);
      this.state.all = response.payload;
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to fetch macros';
      console.error('Error fetching macros:', error);
    } finally {
      this.state.isLoading = false;
    }
  }

  async fetchSingleMacro(macroId: number) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return null;

    try {
      const macro = await macrosApi.getSingleMacro(accountId, macroId);
      // Update in list if exists, otherwise add
      const index = this.state.all.findIndex(m => m.id === macroId);
      if (index >= 0) {
        this.state.all[index] = macro;
      } else {
        this.state.all = [...this.state.all, macro];
      }
      return macro;
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to fetch macro';
      console.error('Error fetching macro:', error);
      return null;
    }
  }

  async createMacro(params: CreateMacroParams) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return null;

    this.state.isSaving = true;
    this.state.error = null;

    try {
      const macro = await macrosApi.createMacro(accountId, params);
      this.state.all = [...this.state.all, macro];
      return macro;
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to create macro';
      console.error('Error creating macro:', error);
      return null;
    } finally {
      this.state.isSaving = false;
    }
  }

  async updateMacro(macroId: number, params: UpdateMacroParams) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return null;

    this.state.isSaving = true;
    this.state.error = null;

    // Optimistic update
    const oldMacro = this.state.all.find(m => m.id === macroId);
    if (oldMacro) {
      this.state.all = this.state.all.map(m => 
        m.id === macroId ? { ...m, ...params } : m
      );
    }

    try {
      const updated = await macrosApi.updateMacro(accountId, macroId, params);
      this.state.all = this.state.all.map(m => 
        m.id === macroId ? updated : m
      );
      return updated;
    } catch (error) {
      // Rollback on error
      if (oldMacro) {
        this.state.all = this.state.all.map(m => 
          m.id === macroId ? oldMacro : m
        );
      }
      this.state.error = error instanceof Error ? error.message : 'Failed to update macro';
      console.error('Error updating macro:', error);
      return null;
    } finally {
      this.state.isSaving = false;
    }
  }

  async deleteMacro(macroId: number) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return false;

    this.state.isDeleting = true;
    this.state.error = null;

    // Optimistic delete
    const deletedMacro = this.state.all.find(m => m.id === macroId);
    this.state.all = this.state.all.filter(m => m.id !== macroId);

    try {
      await macrosApi.deleteMacro(accountId, macroId);
      return true;
    } catch (error) {
      // Rollback on error
      if (deletedMacro) {
        this.state.all = [...this.state.all, deletedMacro];
      }
      this.state.error = error instanceof Error ? error.message : 'Failed to delete macro';
      console.error('Error deleting macro:', error);
      return false;
    } finally {
      this.state.isDeleting = false;
    }
  }

  async executeMacro(macroId: number, conversationIds: number[]) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return false;

    this.state.isExecuting = true;
    this.state.error = null;

    try {
      await macrosApi.executeMacro(accountId, macroId, conversationIds);
      return true;
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to execute macro';
      console.error('Error executing macro:', error);
      return false;
    } finally {
      this.state.isExecuting = false;
    }
  }

  selectMacro(id: number | null) {
    this.state.selectedId = id;
  }

  getMacroById(id: number) {
    return this.state.all.find(m => m.id === id) || null;
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
      isExecuting: false,
      error: null
    };
  }
}

export const macrosStore = new MacrosStore();
