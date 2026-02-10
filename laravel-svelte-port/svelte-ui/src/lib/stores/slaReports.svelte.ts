/**
 * SLA Reports Store using Svelte 5 runes
 * Manages SLA (Service Level Agreement) reports and metrics
 */

import { api } from '$lib/api/client';
import type { PaginatedResponse } from '$lib/api/types';

/**
 * SLA Report interface
 */
export interface SLAReport {
  id: number;
  conversationId: number;
  slaId: number;
  slaName: string;
  status: 'hit' | 'missed';
  targetTime: number;
  actualTime: number;
  createdAt: string;
  conversation?: {
    id: number;
    displayId: number;
    inboxId: number;
  };
  assignedAgent?: {
    id: number;
    name: string;
  };
}

/**
 * SLA Metrics interface
 */
export interface SLAMetrics {
  totalConversations: number;
  hitCount: number;
  missedCount: number;
  hitRate: number;
  averageResponseTime: number;
  averageResolutionTime: number;
}

/**
 * SLA Filter params
 */
export interface SLAFilterParams {
  from: number;
  to: number;
  slaId?: number | null;
  agentId?: number | null;
  inboxId?: number | null;
  teamId?: number | null;
  page?: number;
}

/**
 * SLA Reports Store using Svelte 5 runes
 */
class SLAReportsStore {
  // Reactive state
  reports = $state<SLAReport[]>([]);
  metrics = $state<SLAMetrics | null>(null);
  meta = $state<{ count: number; currentPage: number; perPage: number } | null>(null);
  
  // UI flags
  uiFlags = $state({
    isFetching: false,
    isFetchingMetrics: false,
    isDownloading: false,
  });

  error = $state<string | null>(null);

  /**
   * Get all SLA reports
   */
  getAll() {
    return this.reports;
  }

  /**
   * Get pagination meta
   */
  getMeta() {
    return this.meta;
  }

  /**
   * Get UI flags
   */
  getUIFlags() {
    return this.uiFlags;
  }

  /**
   * Fetch SLA reports
   */
  async get(params: SLAFilterParams & { accountId: number }) {
    this.uiFlags.isFetching = true;
    this.error = null;

    try {
      const response = await api
        .get(`accounts/${params.accountId}/reports/sla`, {
          searchParams: {
            from: params.from,
            to: params.to,
            sla_id: params.slaId ?? undefined,
            agent_id: params.agentId ?? undefined,
            inbox_id: params.inboxId ?? undefined,
            team_id: params.teamId ?? undefined,
            page: params.page || 1,
          },
        })
        .json<PaginatedResponse<SLAReport>>();

      this.reports = response.data;
      this.meta = {
        count: response.meta.totalCount,
        currentPage: response.meta.currentPage,
        perPage: response.meta.totalPages,
      };
    } catch (err) {
      this.error = err instanceof Error ? err.message : 'Failed to fetch SLA reports';
      console.error('SLA reports fetch error:', err);
    } finally {
      this.uiFlags.isFetching = false;
    }
  }

  /**
   * Fetch SLA metrics
   */
  async fetchMetrics(params: Omit<SLAFilterParams, 'page'> & { accountId: number }) {
    this.uiFlags.isFetchingMetrics = true;
    this.error = null;

    try {
      const response = await api
        .get(`accounts/${params.accountId}/reports/sla/metrics`, {
          searchParams: {
            from: params.from,
            to: params.to,
            sla_id: params.slaId ?? undefined,
            agent_id: params.agentId ?? undefined,
            inbox_id: params.inboxId ?? undefined,
            team_id: params.teamId ?? undefined,
          },
        })
        .json<SLAMetrics>();

      this.metrics = response;
    } catch (err) {
      this.error = err instanceof Error ? err.message : 'Failed to fetch SLA metrics';
      console.error('SLA metrics fetch error:', err);
    } finally {
      this.uiFlags.isFetchingMetrics = false;
    }
  }

  /**
   * Download SLA reports as CSV
   */
  async download(params: SLAFilterParams & { accountId: number; fileName: string }) {
    this.uiFlags.isDownloading = true;
    this.error = null;

    try {
      const response = await api
        .get(`accounts/${params.accountId}/reports/sla/download`, {
          searchParams: {
            from: params.from,
            to: params.to,
            sla_id: params.slaId ?? undefined,
            agent_id: params.agentId ?? undefined,
            inbox_id: params.inboxId ?? undefined,
            team_id: params.teamId ?? undefined,
          },
        })
        .blob();

      // Trigger download
      const url = URL.createObjectURL(response);
      const link = document.createElement('a');
      link.href = url;
      link.download = params.fileName;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
    } catch (err) {
      this.error = err instanceof Error ? err.message : 'Failed to download SLA reports';
      console.error('SLA download error:', err);
    } finally {
      this.uiFlags.isDownloading = false;
    }
  }

  /**
   * Reset store state
   */
  reset() {
    this.reports = [];
    this.metrics = null;
    this.meta = null;
    this.error = null;
    this.uiFlags = {
      isFetching: false,
      isFetchingMetrics: false,
      isDownloading: false,
    };
  }
}

// Export singleton instance
export const slaReportsStore = new SLAReportsStore();
