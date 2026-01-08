/**
 * SLA (Service Level Agreement) API Client
 * Handles SLA policy management and tracking
 */

import api from './client';

export interface SLAPolicy {
  id: number;
  name: string;
  description?: string;
  firstResponseTime: number; // seconds
  nextResponseTime: number; // seconds
  resolutionTime: number; // seconds
  onlyDuringBusinessHours: boolean;
  accountId: number;
  createdAt: string;
  updatedAt: string;
}

export interface CreateSLAPolicyParams {
  name: string;
  description?: string;
  firstResponseTime: number;
  nextResponseTime: number;
  resolutionTime: number;
  onlyDuringBusinessHours?: boolean;
}

export interface UpdateSLAPolicyParams {
  name?: string;
  description?: string;
  firstResponseTime?: number;
  nextResponseTime?: number;
  resolutionTime?: number;
  onlyDuringBusinessHours?: boolean;
}

export interface SLAPolicyListResponse {
  payload: SLAPolicy[];
}

/**
 * Get all SLA policies for account
 */
export async function getSLAPolicies(
  accountId: number
): Promise<SLAPolicyListResponse> {
  return api.get(`api/v1/accounts/${accountId}/sla_policies`).json();
}

/**
 * Get single SLA policy
 */
export async function getSLAPolicy(
  accountId: number,
  policyId: number
): Promise<{ payload: SLAPolicy }> {
  return api.get(`api/v1/accounts/${accountId}/sla_policies/${policyId}`).json();
}

/**
 * Create new SLA policy
 */
export async function createSLAPolicy(
  accountId: number,
  params: CreateSLAPolicyParams
): Promise<SLAPolicy> {
  const response = await api.post(`api/v1/accounts/${accountId}/sla_policies`, {
    json: params
  }).json<{ payload: SLAPolicy }>();
  return response.payload;
}

/**
 * Update existing SLA policy
 */
export async function updateSLAPolicy(
  accountId: number,
  policyId: number,
  params: UpdateSLAPolicyParams
): Promise<SLAPolicy> {
  const response = await api.patch(`api/v1/accounts/${accountId}/sla_policies/${policyId}`, {
    json: params
  }).json<{ payload: SLAPolicy }>();
  return response.payload;
}

/**
 * Delete SLA policy
 */
export async function deleteSLAPolicy(
  accountId: number,
  policyId: number
): Promise<void> {
  await api.delete(`api/v1/accounts/${accountId}/sla_policies/${policyId}`).json();
}
