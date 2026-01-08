/**
 * Audit Logs Store
 * Manages audit logs using Svelte 5 runes
 */

import * as auditLogsApi from '$lib/api/auditLogs';
import { authStore } from './auth.svelte';
import type { AuditLog, AuditLogFilters } from '$lib/api/auditLogs';

interface AuditLogsState {
  logs: AuditLog[];
  filters: AuditLogFilters;
  isLoading: boolean;
  isExporting: boolean;
  error: string | null;
  currentPage: number;
  totalPages: number;
  totalCount: number;
}

class AuditLogsStore {
  private state = $state<AuditLogsState>({
    logs: [],
    filters: {},
    isLoading: false,
    isExporting: false,
    error: null,
    currentPage: 1,
    totalPages: 1,
    totalCount: 0
  });

  // Getters
  get logs() {
    return this.state.logs;
  }

  get filters() {
    return this.state.filters;
  }

  get isLoading() {
    return this.state.isLoading;
  }

  get isExporting() {
    return this.state.isExporting;
  }

  get error() {
    return this.state.error;
  }

  get currentPage() {
    return this.state.currentPage;
  }

  get totalPages() {
    return this.state.totalPages;
  }

  get totalCount() {
    return this.state.totalCount;
  }

  // Derived getters
  get hasLogs() {
    return (this.state.logs.length > 0);
  }

  get hasMorePages() {
    return (this.state.currentPage < this.state.totalPages);
  }

  // Actions
  async fetchLogs(filters?: AuditLogFilters, page: number = 1) {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    const mergedFilters = { ...this.state.filters, ...filters };
    this.state.filters = mergedFilters;
    this.state.isLoading = true;
    this.state.error = null;

    try {
      const response = await auditLogsApi.getAuditLogs(accountId, {
        ...mergedFilters,
        page
      });
      
      if (page === 1) {
        this.state.logs = response.data;
      } else {
        this.state.logs = [...this.state.logs, ...response.data];
      }
      
      this.state.currentPage = response.meta.currentPage;
      this.state.totalPages = response.meta.totalPages;
      this.state.totalCount = response.meta.totalCount;
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to fetch audit logs';
      console.error('Error fetching audit logs:', error);
    } finally {
      this.state.isLoading = false;
    }
  }

  async loadMore() {
    if (!this.hasMorePages || this.state.isLoading) return;
    
    await this.fetchLogs(this.state.filters, this.state.currentPage + 1);
  }

  async exportLogs() {
    const accountId = authStore.currentAccount?.id;
    if (!accountId) return;

    this.state.isExporting = true;
    this.state.error = null;

    try {
      const blob = await auditLogsApi.exportAuditLogs(accountId, this.state.filters);
      
      // Create download link
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `audit-logs-${new Date().toISOString().split('T')[0]}.csv`;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
      document.body.removeChild(a);
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to export audit logs';
      console.error('Error exporting audit logs:', error);
    } finally {
      this.state.isExporting = false;
    }
  }

  setFilters(filters: AuditLogFilters) {
    this.state.filters = {
      ...this.state.filters,
      ...filters
    };
  }

  clearFilters() {
    this.state.filters = {};
  }

  clearError() {
    this.state.error = null;
  }

  reset() {
    this.state = {
      logs: [],
      filters: {},
      isLoading: false,
      isExporting: false,
      error: null,
      currentPage: 1,
      totalPages: 1,
      totalCount: 0
    };
  }
}

export const auditLogsStore = new AuditLogsStore();
