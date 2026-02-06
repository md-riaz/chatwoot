/**
 * Reports Store
 * Manages analytics and reporting data using Svelte 5 runes
 * Enhanced to match Vue reports store structure
 */

import * as reportsApi from '$lib/api/reports';
import { authStore } from './auth.svelte';
import type { 
  ConversationMetrics, 
  AgentMetrics, 
  TeamMetrics,
  ReportFilters 
} from '$lib/api/reports';

// Live metrics interfaces (matching Vue structure)
export interface LiveAccountMetric {
  open: number;
  unattended: number;
  unassigned: number;
  pending: number;
}

export interface LiveAgentMetric {
  assigneeId: number;
  open: number;
  unattended: number;
}

export interface LiveTeamMetric {
  teamId: number;
  open: number;
  unattended: number;
}

export interface AgentStatusMetric {
  online: number;
  busy: number;
  offline: number;
}

export interface HeatmapData {
  timestamp: number; // Unix timestamp
  value: number;     // Metric value
}

interface ReportsState {
  // Existing metrics (historical data)
  conversationMetrics: ConversationMetrics | null;
  agentMetrics: AgentMetrics[];
  teamMetrics: TeamMetrics[];
  
  // Live metrics (real-time data)
  overview: {
    accountConversationMetric: LiveAccountMetric;
    agentConversationMetric: LiveAgentMetric[];
    teamConversationMetric: LiveTeamMetric[];
    agentStatus: AgentStatusMetric;
    
    // Heatmap data
    accountConversationHeatmap: HeatmapData[];
    accountResolutionHeatmap: HeatmapData[];
    
    // UI flags (granular loading states)
    uiFlags: {
      isFetchingAccountConversationMetric: boolean;
      isFetchingAccountConversationsHeatmap: boolean;
      isFetchingAccountResolutionsHeatmap: boolean;
      isFetchingAgentConversationMetric: boolean;
      isFetchingTeamConversationMetric: boolean;
      isFetchingAgentStatus: boolean;
    };
  };
  
  filters: ReportFilters;
  isLoading: boolean;
  error: string | null;
}

class ReportsStore {
  private state = $state<ReportsState>({
    conversationMetrics: null,
    agentMetrics: [],
    teamMetrics: [],
    
    overview: {
      accountConversationMetric: {
        open: 0,
        unattended: 0,
        unassigned: 0,
        pending: 0
      },
      agentConversationMetric: [],
      teamConversationMetric: [],
      agentStatus: {
        online: 0,
        busy: 0,
        offline: 0
      },
      accountConversationHeatmap: [],
      accountResolutionHeatmap: [],
      uiFlags: {
        isFetchingAccountConversationMetric: false,
        isFetchingAccountConversationsHeatmap: false,
        isFetchingAccountResolutionsHeatmap: false,
        isFetchingAgentConversationMetric: false,
        isFetchingTeamConversationMetric: false,
        isFetchingAgentStatus: false,
      }
    },
    
    filters: {
      since: this.getDefaultStartDate(),
      until: this.getDefaultEndDate()
    },
    isLoading: false,
    error: null
  });

  // Getters - Historical data
  get conversationMetrics() {
    return this.state.conversationMetrics;
  }

  get agentMetrics() {
    return this.state.agentMetrics;
  }

  get teamMetrics() {
    return this.state.teamMetrics;
  }

  // Getters - Live data (matching Vue getters)
  get accountConversationMetric() {
    return this.state.overview.accountConversationMetric;
  }

  get agentConversationMetric() {
    return this.state.overview.agentConversationMetric;
  }

  get teamConversationMetric() {
    return this.state.overview.teamConversationMetric;
  }

  get agentStatus() {
    return this.state.overview.agentStatus;
  }

  get accountConversationHeatmapData() {
    return this.state.overview.accountConversationHeatmap;
  }

  get accountResolutionHeatmapData() {
    return this.state.overview.accountResolutionHeatmap;
  }

  get overviewUIFlags() {
    return this.state.overview.uiFlags;
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
    return [...this.state.agentMetrics]
      .sort((a, b) => b.conversationsCount - a.conversationsCount)
      .slice(0, 5);
  }

  get topTeams() {
    return [...this.state.teamMetrics]
      .sort((a, b) => b.conversationsCount - a.conversationsCount)
      .slice(0, 5);
  }

  get hasData() {
    return !!this.state.conversationMetrics || 
      this.state.agentMetrics.length > 0 || 
      this.state.teamMetrics.length > 0;
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

  // Actions - Live metrics (matching Vue actions)
  async fetchAccountConversationMetric(params: { teamId?: number } = {}) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    this.state.overview.uiFlags.isFetchingAccountConversationMetric = true;
    this.state.error = null;

    try {
      // TODO: Remove mock data when backend is ready
      if (import.meta.env.DEV) {
        // Use mock data in development
        await new Promise(resolve => setTimeout(resolve, 500)); // Simulate API delay
        const { mockAccountConversationMetric } = await import('$lib/components/reports/overview/test-data');
        this.state.overview.accountConversationMetric = mockAccountConversationMetric;
      } else {
        const response = await reportsApi.getLiveConversationMetrics(accountId, params);
        this.state.overview.accountConversationMetric = response.data as LiveAccountMetric;
      }
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to fetch account conversation metrics';
      console.error('Error fetching account conversation metrics:', error);
    } finally {
      this.state.overview.uiFlags.isFetchingAccountConversationMetric = false;
    }
  }

  async fetchAgentConversationMetric() {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    this.state.overview.uiFlags.isFetchingAgentConversationMetric = true;
    this.state.error = null;

    try {
      // TODO: Remove mock data when backend is ready
      if (import.meta.env.DEV) {
        await new Promise(resolve => setTimeout(resolve, 500));
        const { mockAgentConversationMetric } = await import('$lib/components/reports/overview/test-data');
        this.state.overview.agentConversationMetric = mockAgentConversationMetric;
      } else {
        const response = await reportsApi.getLiveGroupedConversations(accountId, { groupBy: 'assignee_id' });
        this.state.overview.agentConversationMetric = response.data as LiveAgentMetric[];
      }
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to fetch agent conversation metrics';
      console.error('Error fetching agent conversation metrics:', error);
    } finally {
      this.state.overview.uiFlags.isFetchingAgentConversationMetric = false;
    }
  }

  async fetchTeamConversationMetric() {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    this.state.overview.uiFlags.isFetchingTeamConversationMetric = true;
    this.state.error = null;

    try {
      // TODO: Remove mock data when backend is ready
      if (import.meta.env.DEV) {
        await new Promise(resolve => setTimeout(resolve, 500));
        const { mockTeamConversationMetric } = await import('$lib/components/reports/overview/test-data');
        this.state.overview.teamConversationMetric = mockTeamConversationMetric;
      } else {
        const response = await reportsApi.getLiveGroupedConversations(accountId, { groupBy: 'team_id' });
        this.state.overview.teamConversationMetric = response.data as LiveTeamMetric[];
      }
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to fetch team conversation metrics';
      console.error('Error fetching team conversation metrics:', error);
    } finally {
      this.state.overview.uiFlags.isFetchingTeamConversationMetric = false;
    }
  }

  async fetchAgentStatus() {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    this.state.overview.uiFlags.isFetchingAgentStatus = true;
    this.state.error = null;

    try {
      // TODO: Remove mock data when backend is ready
      if (import.meta.env.DEV) {
        await new Promise(resolve => setTimeout(resolve, 500));
        const { mockAgentStatus } = await import('$lib/components/reports/overview/test-data');
        this.state.overview.agentStatus = mockAgentStatus;
      } else {
        const response = await reportsApi.getAgentStatus(accountId);
        this.state.overview.agentStatus = response.data as AgentStatusMetric;
      }
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to fetch agent status';
      console.error('Error fetching agent status:', error);
    } finally {
      this.state.overview.uiFlags.isFetchingAgentStatus = false;
    }
  }

  async fetchAccountConversationHeatmap(params: {
    metric: string;
    from: number;
    to: number;
    groupBy: string;
    businessHours?: boolean;
    type?: string;
    id?: number;
  }) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    this.state.overview.uiFlags.isFetchingAccountConversationsHeatmap = true;
    this.state.error = null;

    try {
      // TODO: Remove mock data when backend is ready
      if (import.meta.env.DEV) {
        await new Promise(resolve => setTimeout(resolve, 800));
        const { generateMockHeatmapData } = await import('$lib/components/reports/overview/test-data');
        this.state.overview.accountConversationHeatmap = generateMockHeatmapData(7);
      } else {
        const response = await reportsApi.getHeatmapData(accountId, params);
        this.state.overview.accountConversationHeatmap = response.data as HeatmapData[];
      }
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to fetch conversation heatmap';
      console.error('Error fetching conversation heatmap:', error);
    } finally {
      this.state.overview.uiFlags.isFetchingAccountConversationsHeatmap = false;
    }
  }

  async fetchAccountResolutionHeatmap(params: {
    metric: string;
    from: number;
    to: number;
    groupBy: string;
    businessHours?: boolean;
    type?: string;
    id?: number;
  }) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    this.state.overview.uiFlags.isFetchingAccountResolutionsHeatmap = true;
    this.state.error = null;

    try {
      // TODO: Remove mock data when backend is ready
      if (import.meta.env.DEV) {
        await new Promise(resolve => setTimeout(resolve, 800));
        const { generateMockHeatmapData } = await import('$lib/components/reports/overview/test-data');
        // Generate different data for resolutions (generally lower values)
        const baseData = generateMockHeatmapData(7);
        this.state.overview.accountResolutionHeatmap = baseData.map(item => ({
          ...item,
          value: Math.floor(item.value * 0.7) // Resolutions typically lower than conversations
        }));
      } else {
        const response = await reportsApi.getHeatmapData(accountId, params);
        this.state.overview.accountResolutionHeatmap = response.data as HeatmapData[];
      }
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to fetch resolution heatmap';
      console.error('Error fetching resolution heatmap:', error);
    } finally {
      this.state.overview.uiFlags.isFetchingAccountResolutionsHeatmap = false;
    }
  }

  async downloadAccountConversationHeatmap(params: { daysBefore: number; to: number }) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    try {
      await reportsApi.downloadConversationTrafficCSV(accountId, params);
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to download heatmap data';
      console.error('Error downloading heatmap data:', error);
    }
  }
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
