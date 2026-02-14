import { api, toSearchParams } from './client';
import type { PaginatedResponse } from './types';
import {
  transformContactFromApi,
  transformContactForApi,
} from '$lib/utils/contact-data';

// Contact interfaces
export interface Contact {
  id: number;
  accountId: number;
  name: string;
  email: string | null;
  phoneNumber: string | null;
  identifier: string | null;
  blocked: boolean;
  thumbnail: string | null; // Rails uses 'thumbnail' not 'avatarUrl'
  customAttributes: Record<string, any>;
  additionalAttributes: Record<string, any>;
  lastActivityAt: number | null; // Rails uses Unix timestamp
  createdAt: number; // Rails uses Unix timestamp
  updatedAt?: number; // Rails uses Unix timestamp
  conversationsCount?: number;

  // Computed properties from additionalAttributes (Rails pattern)
  get city(): string | null;
  get country(): string | null;
  get countryCode(): string | null;
  get company(): string | null;
  get avatarUrl(): string | null; // Alias for thumbnail

  // Legacy support for backward compatibility
  availabilityStatus?: string | null;
  conversations?: any[];
  socialProfiles?: SocialProfile[];
}

export interface SocialProfile {
  id: number;
  type: string;
  url: string;
}

export interface ContactListParams {
  page?: number;
  perPage?: number;
  sort?: string;
  q?: string;
  [key: string]: string | number | boolean | undefined;
}

export interface CreateContactParams {
  name?: string;
  email?: string;
  phoneNumber?: string;
  identifier?: string;
  blocked?: boolean;
  customAttributes?: Record<string, any>;
  additionalAttributes?: Record<string, any>;
  socialProfiles?: Partial<SocialProfile>[];
}

export interface UpdateContactParams extends CreateContactParams {
  avatar?: File;
}

export interface ContactFilterParams {
  q?: string;
  labels?: string[];
  inboxId?: number;
  page?: number;
  perPage?: number;
}

// Advanced filter condition (Vue parity)
// API client auto-transforms camelCase to snake_case
export interface AdvancedFilterCondition {
  attributeKey: string;
  filterOperator: string;
  values: string[];
  queryOperator: 'and' | 'or';
}

export interface ImportContactsParams {
  file: File;
}

export interface ContactConversation {
  id: number;
  status: string;
  inboxId: number;
  // ... other conversation fields
}

/**
 * Get paginated list of contacts
 */
export async function getContacts(
  accountId: number,
  params: ContactListParams = {}
): Promise<PaginatedResponse<Contact>> {
  const response = await api
    .get(`api/v1/accounts/${accountId}/contacts`, {
      searchParams: toSearchParams(params),
    })
    .json<PaginatedResponse<any>>();

  // Transform contacts to add computed properties
  return {
    ...response,
    data: response.data?.map(transformContactFromApi) || [],
  } as PaginatedResponse<Contact>;
}

/**
 * Search contacts with query
 */
export async function searchContacts(
  accountId: number,
  query: string,
  page = 1,
  perPage = 15
): Promise<PaginatedResponse<Contact>> {
  const response = await api
    .get(`api/v1/accounts/${accountId}/contacts/search`, {
      searchParams: toSearchParams({ q: query, page, perPage }),
    })
    .json<PaginatedResponse<any>>();

  // Transform contacts to add computed properties
  return {
    ...response,
    data: response.data?.map(transformContactFromApi) || [],
  } as PaginatedResponse<Contact>;
}

/**
 * Filter contacts with advanced filters (Vue parity)
 * Sends filters wrapped in {payload: []} as Vue does
 * API client auto-transforms keys to snake_case
 *
 * IMPORTANT: First filter should NOT have query_operator (Vue parity)
 * Only filters[1+] have query_operator to chain conditions
 */
export async function filterContacts(
  accountId: number,
  filters: AdvancedFilterCondition[],
  page = 1,
  sortAttr = 'name'
): Promise<PaginatedResponse<Contact>> {
  // Transform filters to match Vue API format:
  // - First filter: NO queryOperator
  // - Subsequent filters: include queryOperator
  const transformedPayload = filters.map((filter, index) => {
    if (index === 0) {
      // First filter: exclude queryOperator (Vue parity)
      const { queryOperator, ...rest } = filter;
      return rest;
    }
    // Subsequent filters: keep queryOperator
    return filter;
  });

  const response = await api
    .post(
      `api/v1/accounts/${accountId}/contacts/filter?include_contact_inboxes=false&page=${page}&sort=${sortAttr}`,
      {
        json: { payload: transformedPayload },
      }
    )
    .json<PaginatedResponse<any>>();

  // Transform contacts to add computed properties
  return {
    ...response,
    data: response.data?.map(transformContactFromApi) || [],
  } as PaginatedResponse<Contact>;
}

/**
 * Get active contacts
 */
export async function getActiveContacts(
  accountId: number,
  params: ContactListParams = {}
): Promise<PaginatedResponse<Contact>> {
  const response = await api
    .get(`api/v1/accounts/${accountId}/contacts/active`, {
      searchParams: toSearchParams(params),
    })
    .json<PaginatedResponse<any>>();

  // Transform contacts to add computed properties
  return {
    ...response,
    data: response.data?.map(transformContactFromApi) || [],
  } as PaginatedResponse<Contact>;
}

/**
 * Get single contact by ID
 */
export async function getContact(
  accountId: number,
  contactId: number
): Promise<Contact> {
  const raw = await api
    .get(`api/v1/accounts/${accountId}/contacts/${contactId}`)
    .json<{ data?: any } | any>();
  const contactPayload = raw?.data ?? raw;
  return transformContactFromApi(contactPayload);
}

/**
 * Create new contact
 */
export async function createContact(
  accountId: number,
  params: CreateContactParams
): Promise<Contact> {
  // Transform data to Rails-compatible format
  const apiData = transformContactForApi(params);

  const raw = await api
    .post(`api/v1/accounts/${accountId}/contacts`, {
      json: apiData,
    })
    .json<{ data?: any } | any>();

  const contactPayload = raw?.data ?? raw;
  return transformContactFromApi(contactPayload);
}

/**
 * Update existing contact
 */
export async function updateContact(
  accountId: number,
  contactId: number,
  params: UpdateContactParams
): Promise<Contact> {
  // If avatar file is provided, use FormData
  if (params.avatar) {
    const formData = new FormData();
    formData.append('avatar', params.avatar);

    // Transform other data to Rails format and add to FormData
    const apiData = transformContactForApi(params);
    Object.entries(apiData).forEach(([key, value]) => {
      if (key !== 'avatar' && value !== undefined) {
        if (typeof value === 'object') {
          formData.append(key, JSON.stringify(value));
        } else {
          formData.append(key, String(value));
        }
      }
    });

    const raw = await api
      .patch(`api/v1/accounts/${accountId}/contacts/${contactId}`, {
        body: formData,
      })
      .json<{ data?: any } | any>();

    const contactPayload = raw?.data ?? raw;
    return transformContactFromApi(contactPayload);
  }

  // Otherwise use JSON with Rails-compatible format
  const apiData = transformContactForApi(params);
  const raw = await api
    .patch(`api/v1/accounts/${accountId}/contacts/${contactId}`, {
      json: apiData,
    })
    .json<{ data?: any } | any>();

  const contactPayload = raw?.data ?? raw;
  return transformContactFromApi(contactPayload);
}

/**
 * Delete contact
 */
export async function deleteContact(
  accountId: number,
  contactId: number
): Promise<void> {
  await api.delete(`api/v1/accounts/${accountId}/contacts/${contactId}`);
}

/**
 * Delete contact avatar
 */

export async function toggleContactBlocked(
  accountId: number,
  contactId: number,
  blocked: boolean
): Promise<Contact> {
  const raw = await api
    .patch(`api/v1/accounts/${accountId}/contacts/${contactId}`, {
      json: { blocked },
    })
    .json<{ data?: any } | any>();

  const contactPayload = raw?.data ?? raw;
  return transformContactFromApi(contactPayload);
}

export async function deleteContactAvatar(
  accountId: number,
  contactId: number
): Promise<Contact> {
  const raw = await api
    .delete(`api/v1/accounts/${accountId}/contacts/${contactId}/avatar`)
    .json<{ data?: any } | any>();

  const contactPayload = raw?.data ?? raw;
  return transformContactFromApi(contactPayload);
}

/**
 * Get contact conversations
 */
export async function getContactConversations(
  accountId: number,
  contactId: number
): Promise<ContactConversation[]> {
  return api
    .get(`api/v1/accounts/${accountId}/contacts/${contactId}/conversations`)
    .json();
}

/**
 * Merge contacts (primary absorbs secondary)
 */
export async function mergeContacts(
  accountId: number,
  primaryContactId: number,
  secondaryContactId: number
): Promise<Contact> {
  const raw = await api
    .post(`api/v1/accounts/${accountId}/contacts/${primaryContactId}/merge`, {
      json: { childContactId: secondaryContactId },
    })
    .json<{ data?: any } | any>();

  const contactPayload = raw?.data ?? raw;
  return transformContactFromApi(contactPayload);
}

/**
 * Import contacts from file
 */
export async function importContacts(
  accountId: number,
  file: File
): Promise<{ success: boolean; failed: number; total: number }> {
  const formData = new FormData();
  formData.append('import_file', file);

  return api
    .post(`api/v1/accounts/${accountId}/contacts/import`, {
      body: formData,
    })
    .json();
}

/**
 * Export contacts
 */
export async function exportContacts(accountId: number): Promise<Blob> {
  return api.get(`api/v1/accounts/${accountId}/contacts/export`).blob();
}

/**
 * Bulk assign labels to contacts
 */
export async function bulkAssignLabels(
  accountId: number,
  contactIds: number[],
  labels: string[]
): Promise<{ success: boolean; updated: number }> {
  return api
    .post(`api/v1/accounts/${accountId}/contacts/bulk_actions`, {
      json: {
        type: 'assign_labels',
        contact_ids: contactIds,
        labels: labels,
      },
    })
    .json();
}

/**
 * Bulk delete contacts
 */
export async function bulkDeleteContacts(
  accountId: number,
  contactIds: number[]
): Promise<{ success: boolean; deleted: number }> {
  return api
    .post(`api/v1/accounts/${accountId}/contacts/bulk_actions`, {
      json: {
        type: 'delete',
        contact_ids: contactIds,
      },
    })
    .json();
}

/**
 * Get contact labels
 */
export async function getContactLabels(
  accountId: number,
  contactId: number
): Promise<string[]> {
  return api
    .get(`api/v1/accounts/${accountId}/contacts/${contactId}/labels`)
    .json();
}

/**
 * Update contact labels
 */
export async function updateContactLabels(
  accountId: number,
  contactId: number,
  labels: string[]
): Promise<string[]> {
  return api
    .post(`api/v1/accounts/${accountId}/contacts/${contactId}/labels`, {
      json: { labels },
    })
    .json();
}
