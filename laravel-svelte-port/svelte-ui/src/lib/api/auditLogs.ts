/**
 * Audit Logs API Client
 * Handles audit log fetching and filtering
 */

import api, { toSearchParams } from './client';

export interface AuditLog {
  id: number;
  activityType: string;
  activityMessage: string;
  userId: number;
  userName: string;
  userAvatar?: string;
  resourceType: string;
  resourceId: number;
  metadata?: Record<string, any>;
  createdAt: string;
  accountId: number;
}

export interface AuditLogFilters {
  activityType?: string;
  userId?: number;
  startDate?: string;
  endDate?: string;
  page?: number;
}

export interface AuditLogsResponse {
  data: AuditLog[];
  meta: {
    currentPage: number;
    totalPages: number;
    totalCount: number;
  };
}

/**
 * Get audit logs with filters
 */
export async function getAuditLogs(
  accountId: number,
  filters: AuditLogFilters = {}
): Promise<AuditLogsResponse> {
  return api.get(`api/v1/accounts/${accountId}/audit_logs`, {
    searchParams: toSearchParams({
      ...filters,
      page: filters.page || 1
    })
  }).json();
}

/**
 * Get single audit log
 */
export async function getAuditLog(
  accountId: number,
  logId: number
): Promise<{ data: AuditLog }> {
  return api.get(`api/v1/accounts/${accountId}/audit_logs/${logId}`).json();
}

/**
 * Export audit logs to CSV
 */
export async function exportAuditLogs(
  accountId: number,
  filters: AuditLogFilters = {}
): Promise<Blob> {
  return api.get(`api/v1/accounts/${accountId}/audit_logs/export`, {
    searchParams: toSearchParams(filters)
  }).blob();
}
