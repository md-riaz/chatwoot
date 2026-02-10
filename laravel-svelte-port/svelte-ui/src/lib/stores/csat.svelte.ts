/**
 * CSAT (Customer Satisfaction) Store using Svelte 5 runes
 * Manages CSAT survey responses and metrics
 */

import { api } from '$lib/api/client';
import type { PaginatedResponse } from '$lib/api/types';

/**
 * CSAT Response interface
 */
export interface CSATResponse {
  id: number;
  conversationId: number;
  contactId: number;
  assignedAgentId: number | null;
  rating: number;
  feedbackMessage: string | null;
  createdAt: string;
  contact?: {
    id: number;
    name: string;
    email: string;
  };
  assignedAgent?: {
    id: number;
    name: string;
  };
}

/**
 * CSAT Metrics interface
 */
export interface CSATMetrics {
  totalResponses: number;
  satisfactionScore: number;
  responseRate: number;
  ratingsCount: {
    1: number;
    2: number;
    3: number;
    4: number;
    5: number;
  };
}

/**
 * CSAT Filter params
 */
export interface CSATFilterParams {
  from: number;
  to: number;
  user_ids?: number[];
  inbox_id?: number | null;
  team_id?: number | null;
  rating?: number | null;
  page?: number;
}

/**
 * CSAT Store using Svelte 5 runes
 */
class CSATStore {
  // Reactive state
  responses = $state<CSATResponse[]>([]);
  metrics = $state<CSATMetrics | null>(null);
  meta = $state<{ count: number; currentPage: number; perPage: number } | null>(null);
  
  // UI flags
  uiFlags = $state({
    isFetching: false,
    isFetchingMetrics: false,
    isDownloading: false,
  });

  error = $state<string | null>(null);

  /**
   * Get CSAT responses
   */
  getResponses() {
    return this.responses;
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
   * Fetch CSAT responses
   */
  async get(params: CSATFilterParams) {
    this.uiFlags.isFetching = true;
    this.error = null;

    try {
      const accountId = params.from; // This should come from route/context
      const response = await api
        .get(`accounts/${accountId}/reports/csat`, {
          searchParams: {
            from: params.from,
            to: params.to,
            user_ids: params.user_ids?.join(','),
            inbox_id: params.inbox_id ?? undefined,
            team_id: params.team_id ?? undefined,
            rating: params.rating ?? undefined,
            page: params.page || 1,
          },
        })
        .json<PaginatedResponse<CSATResponse>>();

      this.responses = response.data;
      this.meta = {
        count: response.meta.totalCount,
        currentPage: response.meta.currentPage,
        perPage: response.meta.totalPages,
      };
    } catch (err) {
      this.error = err instanceof Error ? err.message : 'Failed to fetch CSAT responses';
      console.error('CSAT fetch error:', err);
    } finally {
      this.uiFlags.isFetching = false;
    }
  }

  /**
   * Fetch CSAT metrics
   */
  async fetchMetrics(params: Omit<CSATFilterParams, 'page'>) {
    this.uiFlags.isFetchingMetrics = true;
    this.error = null;

    try {
      const accountId = params.from; // This should come from route/context
      const response = await api
        .get(`accounts/${accountId}/reports/csat/metrics`, {
          searchParams: {
            from: params.from,
            to: params.to,
            user_ids: params.user_ids?.join(','),
            inbox_id: params.inbox_id ?? undefined,
            team_id: params.team_id ?? undefined,
            rating: params.rating ?? undefined,
          },
        })
        .json<CSATMetrics>();

      this.metrics = response;
    } catch (err) {
      this.error = err instanceof Error ? err.message : 'Failed to fetch CSAT metrics';
      console.error('CSAT metrics fetch error:', err);
    } finally {
      this.uiFlags.isFetchingMetrics = false;
    }
  }

  /**
   * Download CSAT reports as CSV
   */
  async downloadCSATReports(params: CSATFilterParams & { fileName: string }) {
    this.uiFlags.isDownloading = true;
    this.error = null;

    try {
      const accountId = params.from; // This should come from route/context
      const response = await api
        .get(`accounts/${accountId}/reports/csat/download`, {
          searchParams: {
            from: params.from,
            to: params.to,
            user_ids: params.user_ids?.join(','),
            inbox_id: params.inbox_id ?? undefined,
            team_id: params.team_id ?? undefined,
            rating: params.rating ?? undefined,
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
      this.error = err instanceof Error ? err.message : 'Failed to download CSAT reports';
      console.error('CSAT download error:', err);
    } finally {
      this.uiFlags.isDownloading = false;
    }
  }

  /**
   * Reset store state
   */
  reset() {
    this.responses = [];
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
export const csatStore = new CSATStore();
