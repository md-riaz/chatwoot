/**
 * Reports API Client
 * Handles analytics and reporting data fetching
 */

import api from './client';

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
}

export interface ReportsResponse {
  data: ConversationMetrics | AgentMetrics[] | TeamMetrics[];
  meta?: {
    period: string;
  };
}

/**
 * Get account-level conversation metrics
 */
export async function getAccountReports(
  accountId: number,
  filters: ReportFilters = {}
): Promise<ReportsResponse> {
  return api.get(`api/v2/accounts/${accountId}/reports`, {
    searchParams: filters
  }).json();
}

/**
 * Get agent performance metrics
 */
export async function getAgentReports(
  accountId: number,
  filters: ReportFilters = {}
): Promise<ReportsResponse> {
  return api.get(`api/v2/accounts/${accountId}/reports/agents`, {
    searchParams: filters
  }).json();
}

/**
 * Get team performance metrics
 */
export async function getTeamReports(
  accountId: number,
  filters: ReportFilters = {}
): Promise<ReportsResponse> {
  return api.get(`api/v2/accounts/${accountId}/reports/teams`, {
    searchParams: filters
  }).json();
}

/**
 * Get label-based metrics
 */
export async function getLabelReports(
  accountId: number,
  filters: ReportFilters = {}
): Promise<ReportsResponse> {
  return api.get(`api/v2/accounts/${accountId}/reports/labels`, {
    searchParams: filters
  }).json();
}

/**
 * Get inbox-based metrics
 */
export async function getInboxReports(
  accountId: number,
  filters: ReportFilters = {}
): Promise<ReportsResponse> {
  return api.get(`api/v2/accounts/${accountId}/reports/inboxes`, {
    searchParams: filters
  }).json();
}

/**
 * Get conversation summary
 */
export async function getConversationSummary(
  accountId: number,
  filters: ReportFilters = {}
): Promise<any> {
  return api.get(`api/v2/accounts/${accountId}/reports/summary`, {
    searchParams: filters
  }).json();
}
