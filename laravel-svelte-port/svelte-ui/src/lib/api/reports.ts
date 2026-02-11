/**
 * Reports API Client
 * Handles analytics and reporting data fetching
 * Enhanced to match Vue API structure
 */

import api, { toSearchParams } from './client';

export interface ConversationMetrics {
  totalConversations: number;
  openConversations: number;
  resolvedConversations: number;
  avgFirstResponseTime: number;
  avgResolutionTime: number;
  avgResponseTime: number;
}

export interface AgentMetrics {
  agentId: number;
  agentName: string;
  conversationsCount: number;
  avgFirstResponseTime: number;
  avgResolutionTime: number;
  resolutionCount: number;
}

export interface TeamMetrics {
  teamId: number;
  teamName: string;
  conversationsCount: number;
  avgFirstResponseTime: number;
  avgResolutionTime: number;
}

export interface ReportFilters {
  since?: string;
  until?: string;
  type?: 'account' | 'agent' | 'team' | 'label' | 'inbox';
  id?: number;
  [key: string]: string | number | boolean | undefined;
}

export interface ReportsResponse {
  data: ConversationMetrics | AgentMetrics[] | TeamMetrics[];
  meta?: {
    period: string;
  };
}

// Live metrics interfaces
export interface LiveConversationMetricsResponse {
  data: {
    open: number;
    unattended: number;
    unassigned: number;
    pending: number;
  };
}

export interface LiveGroupedConversationsResponse {
  data: Array<{
    assigneeId?: number;
    teamId?: number;
    open: number;
    unattended: number;
  }>;
}

export interface AgentStatusResponse {
  data: {
    online: number;
    busy: number;
    offline: number;
  };
}

export interface HeatmapDataResponse {
  data: Array<{
    timestamp: number;
    value: number;
  }>;
}

/**
 * Get account-level conversation metrics
 */
export async function getAccountReports(
  accountId: number,
  filters: ReportFilters = {}
): Promise<ReportsResponse> {
  return api.get(`api/v1/accounts/${accountId}/v2/reports`, {
    searchParams: toSearchParams(filters)
  }).json();
}

/**
 * Get agent performance metrics
 */
export async function getAgentReports(
  accountId: number,
  filters: ReportFilters = {}
): Promise<ReportsResponse> {
  return api.get(`api/v1/accounts/${accountId}/v2/reports/agents`, {
    searchParams: toSearchParams(filters)
  }).json();
}

/**
 * Get team performance metrics
 */
export async function getTeamReports(
  accountId: number,
  filters: ReportFilters = {}
): Promise<ReportsResponse> {
  return api.get(`api/v1/accounts/${accountId}/v2/reports/teams`, {
    searchParams: toSearchParams(filters)
  }).json();
}

/**
 * Get label-based metrics
 */
export async function getLabelReports(
  accountId: number,
  filters: ReportFilters = {}
): Promise<ReportsResponse> {
  return api.get(`api/v1/accounts/${accountId}/v2/reports/labels`, {
    searchParams: toSearchParams(filters)
  }).json();
}

/**
 * Get inbox-based metrics
 */
export async function getInboxReports(
  accountId: number,
  filters: ReportFilters = {}
): Promise<ReportsResponse> {
  return api.get(`api/v1/accounts/${accountId}/v2/reports/inboxes`, {
    searchParams: toSearchParams(filters)
  }).json();
}

/**
 * Get conversation summary
 */
export async function getConversationSummary(
  accountId: number,
  filters: ReportFilters = {}
): Promise<any> {
  return api.get(`api/v1/accounts/${accountId}/v2/reports/summary`, {
    searchParams: toSearchParams(filters)
  }).json();
}

/**
 * Live Reports API - Real-time metrics (matching Vue liveReports.js)
 */

/**
 * Get live conversation metrics
 */
export async function getLiveConversationMetrics(
  accountId: number,
  params: { teamId?: number } = {}
): Promise<LiveConversationMetricsResponse> {
  return api.get(`api/v1/accounts/${accountId}/v2/live_reports/conversation_metrics`, {
    searchParams: toSearchParams(params)
  }).json();
}

/**
 * Get live grouped conversations (by assignee_id or team_id)
 */
export async function getLiveGroupedConversations(
  accountId: number,
  params: { groupBy: 'assignee_id' | 'team_id' }
): Promise<LiveGroupedConversationsResponse> {
  return api.get(`api/v1/accounts/${accountId}/v2/live_reports/grouped_conversation_metrics`, {
    searchParams: toSearchParams(params)
  }).json();
}

/**
 * Get agent status metrics (online/busy/offline counts)
 */
export async function getAgentStatus(
  accountId: number
): Promise<AgentStatusResponse> {
  return api.get(`api/v1/accounts/${accountId}/v2/agents/status`).json();
}

/**
 * Get heatmap data (hourly grouped metrics)
 */
export async function getHeatmapData(
  accountId: number,
  params: {
    metric: string;
    from: number;
    to: number;
    groupBy: string;
    businessHours?: boolean;
    type?: string;
    id?: number;
  }
): Promise<HeatmapDataResponse> {
  return api.get(`api/v1/accounts/${accountId}/v2/reports`, {
    searchParams: toSearchParams({
      metric: params.metric,
      since: params.from,
      until: params.to,
      group_by: params.groupBy,
      business_hours: params.businessHours,
      type: params.type,
      id: params.id,
      timezone_offset: -new Date().getTimezoneOffset() / 60
    })
  }).json();
}

/**
 * Download conversation traffic CSV
 */
export async function downloadConversationTrafficCSV(
  accountId: number,
  params: { daysBefore: number; to?: number }
): Promise<void> {
  const response = await api.get(`api/v1/accounts/${accountId}/v2/reports/conversation_traffic`, {
    searchParams: toSearchParams({
      days_before: params.daysBefore,
      timezone_offset: -new Date().getTimezoneOffset() / 60
    })
  });

  // Handle CSV download
  const blob = await response.blob();
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `conversation_traffic_${new Date().toISOString().split('T')[0]}.csv`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  window.URL.revokeObjectURL(url);
}

/**
 * Get account summary (Vue parity)
 */
export async function getAccountSummary(
  accountId: number,
  params: {
    from: number;
    to: number;
    type?: string;
    id?: number;
    groupBy?: string;
    businessHours?: boolean;
  }
): Promise<ReportsResponse> {
  return api.get(`api/v1/accounts/${accountId}/v2/reports/summary`, {
    searchParams: toSearchParams({
      since: params.from,
      until: params.to,
      type: params.type,
      id: params.id,
      group_by: params.groupBy,
      business_hours: params.businessHours,
      timezone_offset: -new Date().getTimezoneOffset() / 60
    })
  }).json();
}

/**
 * Get account report for specific metric (Vue parity)
 */
export async function getAccountReport(
  accountId: number,
  params: {
    metric: string;
    from: number;
    to: number;
    type?: string;
    id?: number;
    groupBy?: string;
    businessHours?: boolean;
  }
): Promise<ReportsResponse> {
  return api.get(`api/v1/accounts/${accountId}/v2/reports`, {
    searchParams: toSearchParams({
      metric: params.metric,
      since: params.from,
      until: params.to,
      type: params.type,
      id: params.id,
      group_by: params.groupBy,
      business_hours: params.businessHours,
      timezone_offset: -new Date().getTimezoneOffset() / 60
    })
  }).json();
}

/**
 * Get bot summary (Vue parity)
 */
export async function getBotSummary(
  accountId: number,
  params: {
    from: number;
    to: number;
    type?: string;
    id?: number;
    groupBy?: string;
    businessHours?: boolean;
  }
): Promise<ReportsResponse> {
  return api.get(`api/v1/accounts/${accountId}/v2/reports/bots/summary`, {
    searchParams: toSearchParams({
      since: params.from,
      until: params.to,
      type: params.type,
      id: params.id,
      group_by: params.groupBy,
      business_hours: params.businessHours,
      timezone_offset: -new Date().getTimezoneOffset() / 60
    })
  }).json();
}

/**
 * Download conversations summary as CSV (Vue parity)
 */
export async function downloadConversationsSummary(
  accountId: number,
  params: {
    from: number;
    to: number;
    fileName?: string;
    businessHours?: boolean;
  }
): Promise<void> {
  const response = await api.get(`api/v1/accounts/${accountId}/v2/reports/conversations/download`, {
    searchParams: toSearchParams({
      since: params.from,
      until: params.to,
      business_hours: params.businessHours,
      timezone_offset: -new Date().getTimezoneOffset() / 60
    })
  });

  // Handle CSV download
  const blob = await response.blob();
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = params.fileName || `conversations_summary_${new Date().toISOString().split('T')[0]}.csv`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  window.URL.revokeObjectURL(url);
}

/**
 * Download agent reports as CSV (Vue parity)
 */
export async function downloadAgentReports(
  accountId: number,
  params: {
    from: number;
    to: number;
    fileName?: string;
    businessHours?: boolean;
  }
): Promise<string> {
  const response = await api.get(`api/v1/accounts/${accountId}/v2/reports/agents`, {
    searchParams: toSearchParams({
      since: params.from,
      until: params.to,
      business_hours: params.businessHours
    })
  });

  return response.text();
}

/**
 * Download label reports as CSV (Vue parity)
 */
export async function downloadLabelReports(
  accountId: number,
  params: {
    from: number;
    to: number;
    fileName?: string;
    businessHours?: boolean;
  }
): Promise<string> {
  const response = await api.get(`api/v1/accounts/${accountId}/v2/reports/labels`, {
    searchParams: toSearchParams({
      since: params.from,
      until: params.to,
      business_hours: params.businessHours
    })
  });

  return response.text();
}

/**
 * Download inbox reports as CSV (Vue parity)
 */
export async function downloadInboxReports(
  accountId: number,
  params: {
    from: number;
    to: number;
    fileName?: string;
    businessHours?: boolean;
  }
): Promise<string> {
  const response = await api.get(`api/v1/accounts/${accountId}/v2/reports/inboxes`, {
    searchParams: toSearchParams({
      since: params.from,
      until: params.to,
      business_hours: params.businessHours
    })
  });

  return response.text();
}

/**
 * Download team reports as CSV (Vue parity)
 */
export async function downloadTeamReports(
  accountId: number,
  params: {
    from: number;
    to: number;
    fileName?: string;
    businessHours?: boolean;
  }
): Promise<string> {
  const response = await api.get(`api/v1/accounts/${accountId}/v2/reports/teams`, {
    searchParams: toSearchParams({
      since: params.from,
      until: params.to,
      business_hours: params.businessHours
    })
  });

  return response.text();
}
