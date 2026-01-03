import { api } from './client';

/**
 * Agent/User interfaces
 */
export interface Agent {
  id: number;
  name: string;
  email: string;
  role: 'administrator' | 'agent';
  availabilityStatus: 'online' | 'offline' | 'busy';
  avatarUrl?: string;
  confirmed: boolean;
  accountId: number;
  customAttributes?: Record<string, any>;
}

export interface AgentListParams {
  page?: number;
  perPage?: number;
}

export interface CreateAgentParams {
  name: string;
  email: string;
  role: 'administrator' | 'agent';
}

export interface UpdateAgentParams {
  name?: string;
  email?: string;
  role?: 'administrator' | 'agent';
  availabilityStatus?: 'online' | 'offline' | 'busy';
}

/**
 * Get list of agents in the account
 */
export async function getAgents(
  accountId: number,
  params?: AgentListParams
): Promise<Agent[]> {
  const searchParams = new URLSearchParams();
  if (params?.page) searchParams.set('page', params.page.toString());
  if (params?.perPage) searchParams.set('per_page', params.perPage.toString());

  const query = searchParams.toString();
  const url = `api/v1/accounts/${accountId}/agents${query ? `?${query}` : ''}`;

  return api.get(url).json();
}

/**
 * Get single agent by ID
 */
export async function getAgent(
  accountId: number,
  agentId: number
): Promise<Agent> {
  return api.get(`api/v1/accounts/${accountId}/agents/${agentId}`).json();
}

/**
 * Create new agent
 */
export async function createAgent(
  accountId: number,
  data: CreateAgentParams
): Promise<Agent> {
  return api
    .post(`api/v1/accounts/${accountId}/agents`, { json: data })
    .json();
}

/**
 * Update agent
 */
export async function updateAgent(
  accountId: number,
  agentId: number,
  data: UpdateAgentParams
): Promise<Agent> {
  return api
    .patch(`api/v1/accounts/${accountId}/agents/${agentId}`, { json: data })
    .json();
}

/**
 * Delete agent
 */
export async function deleteAgent(
  accountId: number,
  agentId: number
): Promise<void> {
  return api.delete(`api/v1/accounts/${accountId}/agents/${agentId}`).json();
}
