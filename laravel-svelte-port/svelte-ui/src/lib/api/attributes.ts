import { api } from './client';

/**
 * Custom Attribute interfaces
 */
export interface CustomAttribute {
  id: number;
  attributeDisplayName: string;
  attributeKey: string;
  attributeDisplayType: 'text' | 'number' | 'date' | 'list' | 'checkbox';
  attributeModel: 'contact_attribute' | 'conversation_attribute';
  attributeValues?: string[];
  defaultValue?: any;
  createdAt: string;
  updatedAt: string;
}

export interface AttributeListParams {
  page?: number;
  perPage?: number;
  attributeModel?: 'contact_attribute' | 'conversation_attribute';
  [key: string]: string | number | boolean | undefined;
}

export interface CreateAttributeParams {
  attributeDisplayName: string;
  attributeKey: string;
  attributeDisplayType: 'text' | 'number' | 'date' | 'list' | 'checkbox';
  attributeModel: 'contact_attribute' | 'conversation_attribute';
  attributeValues?: string[];
  defaultValue?: any;
}

export interface UpdateAttributeParams {
  attributeDisplayName?: string;
  attributeDisplayType?: 'text' | 'number' | 'date' | 'list' | 'checkbox';
  attributeValues?: string[];
  defaultValue?: any;
}

/**
 * Get list of custom attributes
 */
export async function getCustomAttributes(
  accountId: number,
  params?: AttributeListParams
): Promise<CustomAttribute[]> {
  const searchParams = new URLSearchParams();
  if (params?.page) searchParams.set('page', params.page.toString());
  if (params?.perPage) searchParams.set('per_page', params.perPage.toString());
  if (params?.attributeModel)
    searchParams.set('attribute_model', params.attributeModel);

  const query = searchParams.toString();
  const url = `api/v1/accounts/${accountId}/custom_attribute_definitions${query ? `?${query}` : ''}`;

  return api.get(url).json();
}

/**
 * Get single custom attribute
 */
export async function getCustomAttribute(
  accountId: number,
  attributeId: number
): Promise<CustomAttribute> {
  return api
    .get(
      `api/v1/accounts/${accountId}/custom_attribute_definitions/${attributeId}`
    )
    .json();
}

/**
 * Create custom attribute
 */
export async function createCustomAttribute(
  accountId: number,
  data: CreateAttributeParams
): Promise<CustomAttribute> {
  return api
    .post(`api/v1/accounts/${accountId}/custom_attribute_definitions`, {
      json: data,
    })
    .json();
}

/**
 * Update custom attribute
 */
export async function updateCustomAttribute(
  accountId: number,
  attributeId: number,
  data: UpdateAttributeParams
): Promise<CustomAttribute> {
  return api
    .patch(
      `api/v1/accounts/${accountId}/custom_attribute_definitions/${attributeId}`,
      { json: data }
    )
    .json();
}

/**
 * Delete custom attribute
 */
export async function deleteCustomAttribute(
  accountId: number,
  attributeId: number
): Promise<void> {
  return api
    .delete(
      `api/v1/accounts/${accountId}/custom_attribute_definitions/${attributeId}`
    )
    .json();
}
