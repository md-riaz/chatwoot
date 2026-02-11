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
  ReportFilters,
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
  value: number; // Metric value
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
        pending: 0,
      },
      agentConversationMetric: [],
      teamConversationMetric: [],
      agentStatus: {
        online: 0,
        busy: 0,
        offline: 0,
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
      },
    },

    filters: {
      since: this.getDefaultStartDate(),
      until: this.getDefaultEndDate(),
    },
    isLoading: false,
    error: null,
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
    return (
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

  // Actions - Live metrics (matching Vue actions)
  async fetchAccountConversationMetric(params: { teamId?: number } = {}) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    this.state.overview.uiFlags.isFetchingAccountConversationMetric = true;
    this.state.error = null;

    try {
      console.log(
        '🔄 Fetching account conversation metrics with params:',
        params
      );
      const response = await reportsApi.getLiveConversationMetrics(
        accountId,
        params
      );
      this.state.overview.accountConversationMetric =
        response.data as LiveAccountMetric;
      console.log(
        '✅ Account conversation metrics fetched:',
        this.state.overview.accountConversationMetric
      );
    } catch (error) {
      this.state.error =
        error instanceof Error
          ? error.message
          : 'Failed to fetch account conversation metrics';
      console.error('❌ Error fetching account conversation metrics:', error);
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
      console.log('🔄 Fetching agent conversation metrics');
      const response = await reportsApi.getLiveGroupedConversations(accountId, {
        groupBy: 'assignee_id',
      });
      this.state.overview.agentConversationMetric =
        response.data as LiveAgentMetric[];
      console.log(
        '✅ Agent conversation metrics fetched:',
        this.state.overview.agentConversationMetric
      );
    } catch (error) {
      this.state.error =
        error instanceof Error
          ? error.message
          : 'Failed to fetch agent conversation metrics';
      console.error('❌ Error fetching agent conversation metrics:', error);
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
      console.log('🔄 Fetching team conversation metrics');
      const response = await reportsApi.getLiveGroupedConversations(accountId, {
        groupBy: 'team_id',
      });
      this.state.overview.teamConversationMetric =
        response.data as LiveTeamMetric[];
      console.log(
        '✅ Team conversation metrics fetched:',
        this.state.overview.teamConversationMetric
      );
    } catch (error) {
      this.state.error =
        error instanceof Error
          ? error.message
          : 'Failed to fetch team conversation metrics';
      console.error('❌ Error fetching team conversation metrics:', error);
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
      console.log('🔄 Fetching agent status');
      const response = await reportsApi.getAgentStatus(accountId);
      this.state.overview.agentStatus = response.data as AgentStatusMetric;
      console.log('✅ Agent status fetched:', this.state.overview.agentStatus);
    } catch (error) {
      this.state.error =
        error instanceof Error ? error.message : 'Failed to fetch agent status';
      console.error('❌ Error fetching agent status:', error);
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
      console.log('🔄 Fetching conversation heatmap with params:', params);
      const response = await reportsApi.getHeatmapData(accountId, params);
      this.state.overview.accountConversationHeatmap =
        response.data as HeatmapData[];
      console.log('✅ Conversation heatmap fetched:', response.data);
    } catch (error) {
      this.state.error =
        error instanceof Error
          ? error.message
          : 'Failed to fetch conversation heatmap';
      console.error('❌ Error fetching conversation heatmap:', error);
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
      console.log('🔄 Fetching resolution heatmap with params:', params);
      const response = await reportsApi.getHeatmapData(accountId, params);
      this.state.overview.accountResolutionHeatmap =
        response.data as HeatmapData[];
      console.log('✅ Resolution heatmap fetched:', response.data);
    } catch (error) {
      this.state.error =
        error instanceof Error
          ? error.message
          : 'Failed to fetch resolution heatmap';
      console.error('❌ Error fetching resolution heatmap:', error);
    } finally {
      this.state.overview.uiFlags.isFetchingAccountResolutionsHeatmap = false;
    }
  }

  async downloadAccountConversationHeatmap(params: {
    daysBefore: number;
    to: number;
  }) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    try {
      await reportsApi.downloadConversationTrafficCSV(accountId, params);
    } catch (error) {
      this.state.error =
        error instanceof Error
          ? error.message
          : 'Failed to download heatmap data';
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
      const response = await reportsApi.getAccountReports(
        accountId,
        mergedFilters
      );
      this.state.conversationMetrics = response.data as ConversationMetrics;
    } catch (error) {
      this.state.error =
        error instanceof Error ? error.message : 'Failed to fetch reports';
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
      const response = await reportsApi.getAgentReports(
        accountId,
        mergedFilters
      );
      this.state.agentMetrics = response.data as AgentMetrics[];
    } catch (error) {
      this.state.error =
        error instanceof Error
          ? error.message
          : 'Failed to fetch agent reports';
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
      const response = await reportsApi.getTeamReports(
        accountId,
        mergedFilters
      );
      this.state.teamMetrics = response.data as TeamMetrics[];
    } catch (error) {
      this.state.error =
        error instanceof Error ? error.message : 'Failed to fetch team reports';
      console.error('Error fetching team reports:', error);
    } finally {
      this.state.isLoading = false;
    }
  }

  async fetchAllReports(filters?: ReportFilters) {
    await Promise.all([
      this.fetchAccountReports(filters),
      this.fetchAgentReports(filters),
      this.fetchTeamReports(filters),
    ]);
  }

  setDateRange(since: string, until: string) {
    this.state.filters = {
      ...this.state.filters,
      since,
      until,
    };
  }

  setFilters(filters: ReportFilters) {
    this.state.filters = {
      ...this.state.filters,
      ...filters,
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
      overview: {
        accountConversationMetric: {} as LiveAccountMetric,
        agentConversationMetric: [],
        teamConversationMetric: [],
        agentStatus: {} as AgentStatusMetric,
        accountConversationHeatmap: [],
        accountResolutionHeatmap: [],
        uiFlags: {
          isFetchingAccountConversationMetric: false,
          isFetchingAccountConversationsHeatmap: false,
          isFetchingAccountResolutionsHeatmap: false,
          isFetchingAgentConversationMetric: false,
          isFetchingTeamConversationMetric: false,
          isFetchingAgentStatus: false,
        },
      },
      filters: {
        since: this.getDefaultStartDate(),
        until: this.getDefaultEndDate(),
      },
      isLoading: false,
      error: null,
    };
  }

  // Vue parity methods for component compatibility

  /**
   * Get chart data for a specific metric key
   * Vue parity: matches Vuex getter pattern
   */
  getChartData(metricKey: string): any {
    // Return chart data based on metric key
    // This would typically come from a data structure organized by metric keys
    return this.state.conversationMetrics || null;
  }

  /**
   * Get UI flag status for loading states
   * Vue parity: matches Vuex getter pattern for granular loading states
   */
  getUIFlag(flagKey: string): boolean {
    // Check overview UI flags first
    if (flagKey in this.state.overview.uiFlags) {
      return this.state.overview.uiFlags[
        flagKey as keyof typeof this.state.overview.uiFlags
      ];
    }

    // Check for dynamic flag patterns like isFetching_metricName
    if (flagKey.startsWith('isFetching_')) {
      return this.state.isLoading;
    }

    return this.state.isLoading;
  }

  /**
   * Get data by key
   * Vue parity: matches Vuex getter pattern for accessing nested data
   */
  getData(dataKey: string): any {
    // Return data based on key
    if (dataKey === 'accountSummary' || dataKey.includes('summary')) {
      return this.state.conversationMetrics;
    }

    if (dataKey.includes('agent')) {
      return this.state.agentMetrics;
    }

    if (dataKey.includes('team')) {
      return this.state.teamMetrics;
    }

    return null;
  }

  /**
   * Get filter items for dropdowns
   * Vue parity: matches Vuex getter pattern for filter options
   */
  getFilterItems(getterKey: string): any[] {
    // Return filter items based on getter key
    if (getterKey.includes('agent')) {
      return this.state.agentMetrics;
    }

    if (getterKey.includes('team')) {
      return this.state.teamMetrics;
    }

    return [];
  }

  /**
   * Dispatch action dynamically
   * Vue parity: matches Vuex dispatch pattern for dynamic action calls
   */
  async dispatchAction(actionKey: string, params?: any): Promise<void> {
    // Map action keys to methods
    const actionMap: Record<string, (params?: any) => Promise<void>> = {
      fetchAccountConversationMetric: p =>
        this.fetchAccountConversationMetric(p),
      fetchAgentConversationMetric: () => this.fetchAgentConversationMetric(),
      fetchTeamConversationMetric: () => this.fetchTeamConversationMetric(),
      fetchAgentStatus: () => this.fetchAgentStatus(),
      downloadAccountConversationHeatmap: p =>
        this.downloadAccountConversationHeatmap(p),
      downloadAgentReports: p => this.downloadAgentReports(p),
      downloadLabelReports: p => this.downloadLabelReports(p),
      downloadInboxReports: p => this.downloadInboxReports(p),
      downloadTeamReports: p => this.downloadTeamReports(p),
    };

    const action = actionMap[actionKey];
    if (action) {
      await action(params);
    } else {
      console.warn(`Action ${actionKey} not found in reportsStore`);
    }
  }

  /**
   * Fetch account summary
   * Vue parity: matches Vue reports store action
   */
  async fetchAccountSummary(params: {
    from: number;
    to: number;
    type?: string;
    id?: number;
    groupBy?: string;
    businessHours?: boolean;
  }): Promise<void> {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    this.state.isLoading = true;
    this.state.error = null;

    try {
      console.log('🔄 Fetching account summary with params:', params);
      const response = await reportsApi.getAccountSummary(accountId, params);
      this.state.conversationMetrics = response.data as ConversationMetrics;
      console.log('✅ Account summary fetched');
    } catch (error) {
      this.state.error =
        error instanceof Error
          ? error.message
          : 'Failed to fetch account summary';
      console.error('❌ Error fetching account summary:', error);
    } finally {
      this.state.isLoading = false;
    }
  }

  /**
   * Fetch account report for specific metric
   * Vue parity: matches Vue reports store action
   */
  async fetchAccountReport(params: {
    metric: string;
    from: number;
    to: number;
    type?: string;
    id?: number;
    groupBy?: string;
    businessHours?: boolean;
  }): Promise<void> {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    this.state.isLoading = true;
    this.state.error = null;

    try {
      console.log('🔄 Fetching account report with params:', params);
      const response = await reportsApi.getAccountReport(accountId, params);

      // Store the report data - in Vue this would update a specific metric
      // For now, we'll update the main metrics
      this.state.conversationMetrics = response.data as ConversationMetrics;
      console.log('✅ Account report fetched for metric:', params.metric);
    } catch (error) {
      this.state.error =
        error instanceof Error
          ? error.message
          : 'Failed to fetch account report';
      console.error('❌ Error fetching account report:', error);
    } finally {
      this.state.isLoading = false;
    }
  }

  /**
   * Fetch bot summary
   * Vue parity: matches Vue reports store action for bot metrics
   */
  async fetchBotSummary(params: {
    from: number;
    to: number;
    type?: string;
    id?: number;
    groupBy?: string;
    businessHours?: boolean;
  }): Promise<void> {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    this.state.isLoading = true;
    this.state.error = null;

    try {
      console.log('🔄 Fetching bot summary with params:', params);
      const response = await reportsApi.getBotSummary(accountId, params);
      this.state.conversationMetrics = response.data as ConversationMetrics;
      console.log('✅ Bot summary fetched');
    } catch (error) {
      this.state.error =
        error instanceof Error ? error.message : 'Failed to fetch bot summary';
      console.error('❌ Error fetching bot summary:', error);
    } finally {
      this.state.isLoading = false;
    }
  }

  /**
   * Download conversations summary reports as CSV
   * Vue parity: matches Vue reports store action
   */
  async downloadConversationsSummaryReports(params: {
    from: number;
    to: number;
    fileName?: string;
    businessHours?: boolean;
  }): Promise<void> {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    try {
      console.log('🔄 Downloading conversations summary with params:', params);
      await reportsApi.downloadConversationsSummary(accountId, params);
      console.log('✅ Conversations summary download initiated');
    } catch (error) {
      this.state.error =
        error instanceof Error
          ? error.message
          : 'Failed to download conversations summary';
      console.error('❌ Error downloading conversations summary:', error);
    }
  }

  /**
   * Download agent reports as CSV
   * Vue parity: matches Vue reports store action
   */
  async downloadAgentReports(params: {
    from: number;
    to: number;
    fileName: string;
    businessHours?: boolean;
  }): Promise<void> {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    try {
      console.log('🔄 Downloading agent reports with params:', params);
      const csvData = await reportsApi.downloadAgentReports(accountId, params);
      this.downloadCsvFile(params.fileName, csvData);
      console.log('✅ Agent reports download initiated');
    } catch (error) {
      this.state.error =
        error instanceof Error
          ? error.message
          : 'Failed to download agent reports';
      console.error('❌ Error downloading agent reports:', error);
    }
  }

  /**
   * Download label reports as CSV
   * Vue parity: matches Vue reports store action
   */
  async downloadLabelReports(params: {
    from: number;
    to: number;
    fileName: string;
    businessHours?: boolean;
  }): Promise<void> {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    try {
      console.log('🔄 Downloading label reports with params:', params);
      const csvData = await reportsApi.downloadLabelReports(accountId, params);
      this.downloadCsvFile(params.fileName, csvData);
      console.log('✅ Label reports download initiated');
    } catch (error) {
      this.state.error =
        error instanceof Error
          ? error.message
          : 'Failed to download label reports';
      console.error('❌ Error downloading label reports:', error);
    }
  }

  /**
   * Download inbox reports as CSV
   * Vue parity: matches Vue reports store action
   */
  async downloadInboxReports(params: {
    from: number;
    to: number;
    fileName: string;
    businessHours?: boolean;
  }): Promise<void> {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    try {
      console.log('🔄 Downloading inbox reports with params:', params);
      const csvData = await reportsApi.downloadInboxReports(accountId, params);
      this.downloadCsvFile(params.fileName, csvData);
      console.log('✅ Inbox reports download initiated');
    } catch (error) {
      this.state.error =
        error instanceof Error
          ? error.message
          : 'Failed to download inbox reports';
      console.error('❌ Error downloading inbox reports:', error);
    }
  }

  /**
   * Download team reports as CSV
   * Vue parity: matches Vue reports store action
   */
  async downloadTeamReports(params: {
    from: number;
    to: number;
    fileName: string;
    businessHours?: boolean;
  }): Promise<void> {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    try {
      console.log('🔄 Downloading team reports with params:', params);
      const csvData = await reportsApi.downloadTeamReports(accountId, params);
      this.downloadCsvFile(params.fileName, csvData);
      console.log('✅ Team reports download initiated');
    } catch (error) {
      this.state.error =
        error instanceof Error
          ? error.message
          : 'Failed to download team reports';
      console.error('❌ Error downloading team reports:', error);
    }
  }

  /**
   * Helper method to download CSV file
   * Vue parity: matches downloadCsvFile helper from Vue
   */
  private downloadCsvFile(fileName: string, data: string): void {
    const blob = new Blob([data], { type: 'text/csv;charset=utf-8;' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = fileName;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  }
}

export const reportsStore = new ReportsStore();
