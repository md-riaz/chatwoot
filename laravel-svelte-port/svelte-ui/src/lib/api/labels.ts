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
export async function getLabels(params?: LabelListParams): Promise<Label[]> {
  const response = await api.get('labels', {
    searchParams: toSearchParams(params),
  }).json<Label[]>();
  return response;
}

/**
 * Get single label by ID
 */
export async function getLabel(labelId: number): Promise<Label> {
  const response = await api.get(`labels/${labelId}`).json<Label>();
  return response;
}

/**
 * Create new label
 */
export async function createLabel(params: CreateLabelParams): Promise<Label> {
  const response = await api.post('labels', {
    json: params,
  }).json<Label>();
  return response;
}

/**
 * Update label
 */
export async function updateLabel(labelId: number, params: UpdateLabelParams): Promise<Label> {
  const response = await api.patch(`labels/${labelId}`, {
    json: params,
  }).json<Label>();
  return response;
}

/**
 * Delete label
 */
export async function deleteLabel(labelId: number): Promise<void> {
  await api.delete(`labels/${labelId}`);
}
