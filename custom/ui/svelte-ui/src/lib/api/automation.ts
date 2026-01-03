/**
 * Automation Rules API Client
 * Handles all automation rule operations including CRUD and file attachments
 */

import api from './client';

export interface AutomationCondition {
  attributeKey: string;
  filterOperator: string;
  values: string[] | number[];
  customAttributeType?: string;
}

export interface AutomationAction {
  actionName: string;
  actionParams?: any[];
}

export interface Automation {
  id: number;
  name: string;
  description?: string;
  eventName: string;
  conditions: AutomationCondition[];
  actions: AutomationAction[];
  active: boolean;
  accountId: number;
  createdAt: string;
  updatedAt: string;
}

export interface CreateAutomationParams {
  name: string;
  description?: string;
  eventName: string;
  conditions: AutomationCondition[];
  actions: AutomationAction[];
  active?: boolean;
}

export interface UpdateAutomationParams {
  name?: string;
  description?: string;
  eventName?: string;
  conditions?: AutomationCondition[];
  actions?: AutomationAction[];
  active?: boolean;
}

export interface AutomationListResponse {
  payload: Automation[];
}

/**
 * Get all automation rules for account
 */
export async function getAutomations(
  accountId: number
): Promise<AutomationListResponse> {
  return api.get(`api/v1/accounts/${accountId}/automation_rules`).json();
}

/**
 * Create new automation rule
 */
export async function createAutomation(
  accountId: number,
  params: CreateAutomationParams
): Promise<Automation> {
  const response = await api.post(`api/v1/accounts/${accountId}/automation_rules`, {
    json: params
  }).json<{ payload: Automation }>();
  return response.payload;
}

/**
 * Update existing automation rule
 */
export async function updateAutomation(
  accountId: number,
  automationId: number,
  params: UpdateAutomationParams
): Promise<Automation> {
  const response = await api.patch(`api/v1/accounts/${accountId}/automation_rules/${automationId}`, {
    json: params
  }).json<{ payload: Automation }>();
  return response.payload;
}

/**
 * Delete automation rule
 */
export async function deleteAutomation(
  accountId: number,
  automationId: number
): Promise<void> {
  await api.delete(`api/v1/accounts/${accountId}/automation_rules/${automationId}`).json();
}

/**
 * Clone automation rule
 */
export async function cloneAutomation(
  accountId: number,
  automationId: number
): Promise<Automation> {
  const response = await api.post(`api/v1/accounts/${accountId}/automation_rules/${automationId}/clone`).json<{ payload: Automation }>();
  return response.payload;
}

/**
 * Attach file to automation action
 */
export async function attachFile(
  accountId: number,
  file: File
): Promise<{ id: string; url: string }> {
  const formData = new FormData();
  formData.append('attachment', file);
  
  return api.post(`api/v1/accounts/${accountId}/automation_rules/attach_file`, {
    body: formData
  }).json();
}
