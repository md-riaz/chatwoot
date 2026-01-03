/**
 * Macros API Client
 * Handles all macro operations including CRUD and execution
 */

import api from './client';

export interface MacroAction {
  actionName: string;
  actionParams?: any[];
}

export interface Macro {
  id: number;
  name: string;
  visibility: 'global' | 'personal' | 'team';
  actions: MacroAction[];
  accountId: number;
  createdBy: number;
  createdAt: string;
  updatedAt: string;
}

export interface CreateMacroParams {
  name: string;
  visibility: 'global' | 'personal' | 'team';
  actions: MacroAction[];
}

export interface UpdateMacroParams {
  name?: string;
  visibility?: 'global' | 'personal' | 'team';
  actions?: MacroAction[];
}

export interface MacroListResponse {
  payload: Macro[];
}

/**
 * Get all macros for account
 */
export async function getMacros(
  accountId: number
): Promise<MacroListResponse> {
  return api.get(`api/v1/accounts/${accountId}/macros`).json();
}

/**
 * Get single macro by ID
 */
export async function getSingleMacro(
  accountId: number,
  macroId: number
): Promise<Macro> {
  const response = await api.get(`api/v1/accounts/${accountId}/macros/${macroId}`).json<{ payload: Macro }>();
  return response.payload;
}

/**
 * Create new macro
 */
export async function createMacro(
  accountId: number,
  params: CreateMacroParams
): Promise<Macro> {
  const response = await api.post(`api/v1/accounts/${accountId}/macros`, {
    json: params
  }).json<{ payload: Macro }>();
  return response.payload;
}

/**
 * Update existing macro
 */
export async function updateMacro(
  accountId: number,
  macroId: number,
  params: UpdateMacroParams
): Promise<Macro> {
  const response = await api.patch(`api/v1/accounts/${accountId}/macros/${macroId}`, {
    json: params
  }).json<{ payload: Macro }>();
  return response.payload;
}

/**
 * Delete macro
 */
export async function deleteMacro(
  accountId: number,
  macroId: number
): Promise<void> {
  await api.delete(`api/v1/accounts/${accountId}/macros/${macroId}`).json();
}

/**
 * Execute macro on conversation(s)
 */
export async function executeMacro(
  accountId: number,
  macroId: number,
  conversationIds: number[]
): Promise<void> {
  await api.post(`api/v1/accounts/${accountId}/macros/${macroId}/execute`, {
    json: { conversationIds }
  }).json();
}
