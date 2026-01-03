/**
 * Reports Store
 * Manages analytics and reporting data using Svelte 5 runes
 */

import * as reportsApi from '$lib/api/reports';
import { authStore } from './auth.svelte';
import type { 
  ConversationMetrics, 
  AgentMetrics, 
  TeamMetrics,
  ReportFilters 
} from '$lib/api/reports';

interface ReportsState {
  conversationMetrics: ConversationMetrics | null;
  agentMetrics: AgentMetrics[];
  teamMetrics: TeamMetrics[];
  filters: ReportFilters;
  isLoading: boolean;
  error: string | null;
}

class ReportsStore {
  private state = $state<ReportsState>({
    conversationMetrics: null,
    agentMetrics: [],
    teamMetrics: [],
    filters: {
      since: this.getDefaultStartDate(),
      until: this.getDefaultEndDate()
    },
    isLoading: false,
    error: null
  });

  // Getters
  get conversationMetrics() {
    return this.state.conversationMetrics;
  }

  get agentMetrics() {
    return this.state.agentMetrics;
  }

  get teamMetrics() {
    return this.state.teamMetrics;
  }

  get filters() {
    return this.state.filters;
  }

  get isLoading() {
    return this.state.isLoading;
  }

  get error() {
    return this.state.error;
  }

  // Derived getters
  get topAgents() {
    return $derived(
      [...this.state.agentMetrics]
        .sort((a, b) => b.conversationsCount - a.conversationsCount)
        .slice(0, 5)
    );
  }

  get topTeams() {
    return $derived(
      [...this.state.teamMetrics]
        .sort((a, b) => b.conversationsCount - a.conversationsCount)
        .slice(0, 5)
    );
  }

  get hasData() {
    return $derived(
      !!this.state.conversationMetrics || 
      this.state.agentMetrics.length > 0 || 
      this.state.teamMetrics.length > 0
    );
  }

  // Helper methods
  private getDefaultStartDate(): string {
    const date = new Date();
    date.setDate(date.getDate() - 30); // Last 30 days
    return date.toISOString().split('T')[0];
  }

  private getDefaultEndDate(): string {
    return new Date().toISOString().split('T')[0];
  }

  // Actions
  async fetchAccountReports(filters?: ReportFilters) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    const mergedFilters = { ...this.state.filters, ...filters };
    this.state.filters = mergedFilters;
    this.state.isLoading = true;
    this.state.error = null;

    try {
      const response = await reportsApi.getAccountReports(accountId, mergedFilters);
      this.state.conversationMetrics = response.data as ConversationMetrics;
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to fetch reports';
      console.error('Error fetching account reports:', error);
    } finally {
      this.state.isLoading = false;
    }
  }

  async fetchAgentReports(filters?: ReportFilters) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    const mergedFilters = { ...this.state.filters, ...filters };
    this.state.isLoading = true;
    this.state.error = null;

    try {
      const response = await reportsApi.getAgentReports(accountId, mergedFilters);
      this.state.agentMetrics = response.data as AgentMetrics[];
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to fetch agent reports';
      console.error('Error fetching agent reports:', error);
    } finally {
      this.state.isLoading = false;
    }
  }

  async fetchTeamReports(filters?: ReportFilters) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    const mergedFilters = { ...this.state.filters, ...filters };
    this.state.isLoading = true;
    this.state.error = null;

    try {
      const response = await reportsApi.getTeamReports(accountId, mergedFilters);
      this.state.teamMetrics = response.data as TeamMetrics[];
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to fetch team reports';
      console.error('Error fetching team reports:', error);
    } finally {
      this.state.isLoading = false;
    }
  }

  async fetchAllReports(filters?: ReportFilters) {
    await Promise.all([
      this.fetchAccountReports(filters),
      this.fetchAgentReports(filters),
      this.fetchTeamReports(filters)
    ]);
  }

  setDateRange(since: string, until: string) {
    this.state.filters = {
      ...this.state.filters,
      since,
      until
    };
  }

  setFilters(filters: ReportFilters) {
    this.state.filters = {
      ...this.state.filters,
      ...filters
    };
  }

  clearError() {
    this.state.error = null;
  }

  reset() {
    this.state = {
      conversationMetrics: null,
      agentMetrics: [],
      teamMetrics: [],
      filters: {
        since: this.getDefaultStartDate(),
        until: this.getDefaultEndDate()
      },
      isLoading: false,
      error: null
    };
  }
}

export const reportsStore = new ReportsStore();
