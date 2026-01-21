import { api, toSearchParams } from './client';

/**
 * Label interfaces
 */
export interface Label {
  id: number;
  title: string;
  description: string;
  color: string;
  showOnSidebar: boolean;
  conversationsCount?: number;
}

export interface LabelListParams {
  page?: number;
  perPage?: number;
  [key: string]: string | number | boolean | undefined;
}

export interface CreateLabelParams {
  title: string;
  description?: string;
  color: string;
  show_on_sidebar?: boolean;
}

export interface UpdateLabelParams {
  title?: string;
  description?: string;
  color?: string;
  show_on_sidebar?: boolean;
}

/**
 * Get list of labels
 */
export async function getLabels(accountId: number, params?: LabelListParams): Promise<Label[]> {
  const response = await api.get(`api/v1/accounts/${accountId}/labels`, {
    searchParams: toSearchParams(params),
  }).json<{ data: Label[] }>();
  return response.data;
}

/**
 * Get single label by ID
 */
export async function getLabel(accountId: number, labelId: number): Promise<Label> {
  const response = await api.get(`api/v1/accounts/${accountId}/labels/${labelId}`).json<{ data: Label }>();
  return response.data;
}

/**
 * Create new label
 */
export async function createLabel(accountId: number, params: CreateLabelParams): Promise<Label> {
  const response = await api.post(`api/v1/accounts/${accountId}/labels`, {
    json: params,
  }).json<{ data: Label }>();
  return response.data;
}

/**
 * Update label
 */
export async function updateLabel(accountId: number, labelId: number, params: UpdateLabelParams): Promise<Label> {
  const response = await api.patch(`api/v1/accounts/${accountId}/labels/${labelId}`, {
    json: params,
  }).json<{ data: Label }>();
  return response.data;
}

/**
 * Delete label
 */
export async function deleteLabel(accountId: number, labelId: number): Promise<void> {
  await api.delete(`api/v1/accounts/${accountId}/labels/${labelId}`);
}
