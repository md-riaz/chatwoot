/**
 * SLA Store
 * Manages SLA policies using Svelte 5 runes
 */

import * as slaApi from '$lib/api/sla';
import { authStore } from './auth.svelte';
import type { 
  SLAPolicy, 
  CreateSLAPolicyParams, 
  UpdateSLAPolicyParams 
} from '$lib/api/sla';

interface SLAState {
  all: SLAPolicy[];
  selectedId: number | null;
  isLoading: boolean;
  isSaving: boolean;
  isDeleting: boolean;
  error: string | null;
}

class SLAStore {
  private state = $state<SLAState>({
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
  get selectedPolicy() {
    return $derived(
      this.state.all.find(p => p.id === this.state.selectedId) || null
    );
  }

  get sortedPolicies() {
    return $derived(
      [...this.state.all].sort((a, b) => a.name.localeCompare(b.name))
    );
  }

  get policyCount() {
    return $derived(this.state.all.length);
  }

  // Actions
  async fetchPolicies() {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    this.state.isLoading = true;
    this.state.error = null;

    try {
      const response = await slaApi.getSLAPolicies(accountId);
      this.state.all = response.payload;
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to fetch SLA policies';
      console.error('Error fetching SLA policies:', error);
    } finally {
      this.state.isLoading = false;
    }
  }

  async fetchPolicy(policyId: number) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return null;

    try {
      const response = await slaApi.getSLAPolicy(accountId, policyId);
      const policy = response.payload;
      
      // Update in list if exists
      const index = this.state.all.findIndex(p => p.id === policyId);
      if (index >= 0) {
        this.state.all[index] = policy;
      } else {
        this.state.all = [...this.state.all, policy];
      }
      
      return policy;
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to fetch SLA policy';
      console.error('Error fetching SLA policy:', error);
      return null;
    }
  }

  async createPolicy(params: CreateSLAPolicyParams) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return null;

    this.state.isSaving = true;
    this.state.error = null;

    try {
      const policy = await slaApi.createSLAPolicy(accountId, params);
      this.state.all = [...this.state.all, policy];
      return policy;
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to create SLA policy';
      console.error('Error creating SLA policy:', error);
      return null;
    } finally {
      this.state.isSaving = false;
    }
  }

  async updatePolicy(policyId: number, params: UpdateSLAPolicyParams) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return null;

    this.state.isSaving = true;
    this.state.error = null;

    // Optimistic update
    const oldPolicy = this.state.all.find(p => p.id === policyId);
    if (oldPolicy) {
      this.state.all = this.state.all.map(p => 
        p.id === policyId ? { ...p, ...params } : p
      );
    }

    try {
      const updated = await slaApi.updateSLAPolicy(accountId, policyId, params);
      this.state.all = this.state.all.map(p => 
        p.id === policyId ? updated : p
      );
      return updated;
    } catch (error) {
      // Rollback on error
      if (oldPolicy) {
        this.state.all = this.state.all.map(p => 
          p.id === policyId ? oldPolicy : p
        );
      }
      this.state.error = error instanceof Error ? error.message : 'Failed to update SLA policy';
      console.error('Error updating SLA policy:', error);
      return null;
    } finally {
      this.state.isSaving = false;
    }
  }

  async deletePolicy(policyId: number) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return false;

    this.state.isDeleting = true;
    this.state.error = null;

    // Optimistic delete
    const deletedPolicy = this.state.all.find(p => p.id === policyId);
    this.state.all = this.state.all.filter(p => p.id !== policyId);

    try {
      await slaApi.deleteSLAPolicy(accountId, policyId);
      return true;
    } catch (error) {
      // Rollback on error
      if (deletedPolicy) {
        this.state.all = [...this.state.all, deletedPolicy];
      }
      this.state.error = error instanceof Error ? error.message : 'Failed to delete SLA policy';
      console.error('Error deleting SLA policy:', error);
      return false;
    } finally {
      this.state.isDeleting = false;
    }
  }

  selectPolicy(id: number | null) {
    this.state.selectedId = id;
  }

  getPolicyById(id: number) {
    return this.state.all.find(p => p.id === id) || null;
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

export const slaStore = new SLAStore();
