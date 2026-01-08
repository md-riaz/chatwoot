/**
 * Agent API
 * 
 * API methods for getting agent information.
 */

import { getWidgetApi } from './client';
import type { Agent, ApiResponse } from './types';

/**
 * Get available agents
 */
export async function getAvailableAgents(): Promise<Agent[]> {
  const api = getWidgetApi();
  const response = await api.get('agents').json<ApiResponse<Agent[]>>();
  return response.data;
}

/**
 * Get agent availability status
 */
export async function getAgentAvailability(): Promise<{
  available: boolean;
  agents: Agent[];
}> {
  const api = getWidgetApi();
  const response = await api.get('agents/availability').json<ApiResponse<any>>();
  return response.data;
}
